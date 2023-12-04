<?php
// Load WordPress
define('WP_USE_THEMES', false);
require_once('../../../../wp-load.php');

// Endpoint URLs
$etherscanUrl = "https://api.etherscan.io/api";
$loopringUrl = "https://api3.loopring.io/api/v3/account";
$loopringNFTUrl = "https://api3.loopring.io/api/v3/user/nft/balances";

// Check if a request was made
if (isset($_GET['url'])) {
    $url = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL);
    $headers = array('Content-Type: application/json');
    $loopringApiKey = get_option('loopring_api_key');
    // Retrieve the wallet address from the request
    $owner = filter_input(INPUT_GET, 'owner', FILTER_SANITIZE_STRING);
    // Find the user by wallet address
    $user_query = new WP_User_Query(array(
        'meta_key' => 'wallet_address',
        'meta_value' => $owner
    ));
    $users = $user_query->get_results();

    if (empty($users)) {
        http_response_code(403); // Forbidden
        exit('User not found.');
    }

    // Get the user ID
    $user_id = $users[0]->ID;

    // Retrieve the proxy key from user meta
    $user_proxy_key = get_user_meta($user_id, 'proxy_api_key', true);

    // Check the proxy key
    if (!isset($_SERVER['HTTP_X_PROXY_KEY']) || $_SERVER['HTTP_X_PROXY_KEY'] !== $user_proxy_key) {
        http_response_code(403); // Forbidden
        exit('Access denied.');
    }
    // Check which endpoint was requested
    if (strpos($url, $etherscanUrl) !== false) {
        // Etherscan endpoint requested
        $apiKey = ''; // Put your Etherscan API key here
        $url .= "?apikey=$apiKey";
    } elseif (strpos($url, $loopringUrl) !== false) {
        // Loopring endpoint requested
        $url .= "?owner=$owner";
        $headers = array('Content-Type: application/json', "X-API-KEY: $loopringApiKey");
    } elseif (strpos($url, $loopringNFTUrl) !== false) {
        // Loopring NFT endpoint requested
        if (isset($_GET['tokenAddrs'])) {
            $token = filter_input(INPUT_GET, 'tokenAddrs', FILTER_SANITIZE_STRING);
            $url .= "?accountId=$owner&tokenAddrs=$token";
        } else {
            $url .= "?accountId=$owner";
        }

        $headers = array('Content-Type: application/json', "X-API-KEY: $loopringApiKey");

        // Fetch the NFTs from the Loopring API
        $nfts = fetchNFTs($url, $headers);
        // Clean up the NFT array
        $nfts_to_save = array();
        foreach ($nfts as $nft) {
            $nfts_to_save[] = array(
                'minter' => $nft['minter'],
                'tokenAddress' => $nft['tokenAddress'],
                'nftId' => $nft['nftId'],
            );
        }
        // Save the fetched NFTs to the user's meta data
        update_user_meta($user_id, 'nfts', $nfts_to_save);
        // Update the user's role
        assign_role_by_nft(wp_get_current_user());
        
        echo json_encode($nfts);
        return;
    } else {
        // Invalid endpoint requested
        http_response_code(404);
        echo "Invalid endpoint requested";
        exit;
    }

    // Create a cURL request to the endpoint
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // set timeout to 60 seconds
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Relay the response
    http_response_code($httpCode);
    echo $response;
} else {
    // No request made
    http_response_code(400);
    echo "No request made";
}

function fetchNFTs($url, $headers) {
    $nfts = array();
    $offset = 0;
    do {
        $url_with_offset = $url . "&offset=$offset";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // set timeout to 60 seconds
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url_with_offset);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode != 200) {
            return [];
        }

        $json = json_decode($response, true);
        $nfts = array_merge($nfts, $json['data']);
        $offset += count($json['data']);
    } while ($offset < $json['totalNum']);
    return $nfts;
}

function assign_role_by_nft($user) {
    // Retrieve the NFTs
    if (user_can($user->ID, 'administrator')) {
        return;
    }
    $nfts = get_user_meta($user->ID, 'nfts', true);
    $nft_roles = get_option('nft_roles', []);
    $found = false;
    $defaults = array(
        'minter' => '',
        'token' => '',
        'nft_id' => ''
    );
    foreach ($nft_roles as $role_name => $role_attributes) {
        $role_attributes = shortcode_atts($defaults, $role_attributes);
        foreach ($nfts as $nft) {
            $nft_conditions = check_conditions($nft, $role_attributes);
            if ($nft_conditions) {
                error_log("Setting role to: $role_name"); // Debug log
                $found = true;
                // Assign role to user
                $user->set_role($role_name);
                break 2;  // Once role is set, we break out of both loops.
            }
        }
    }
    if(!$found){
        $default_role = get_option('default_nft_role');
        //error_log("Setting role to default: $default_role");  // Debug log
        $user->set_role($default_role);
    }
}