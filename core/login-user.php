<?php
// Include necessary files
require_once('../../../../wp-load.php');
require_once('lib/EthSignature.php');

// AJAX handler function
function handle_ajax_login() {
    check_ajax_referer('ajax-login-nonce', 'security');

    $address = sanitize_text_field($_POST['address']);
    $signedMessage = sanitize_text_field($_POST['signedMessage']);
    $nonceFromClient = sanitize_text_field($_POST['nonceFromClient']);

    $result = handle_signed_message($address, $signedMessage, $nonceFromClient);

    wp_send_json($result);
}

add_action('wp_ajax_nopriv_handle_login', 'handle_ajax_login');
add_action('wp_ajax_handle_login', 'handle_ajax_login');

function generate_transient($address) {
    $nonce = bin2hex(random_bytes(16));
    set_transient('walletconnect_nonce_' . $address, $nonce, 60*5);
    return $nonce;
}

function handle_signed_message($address, $signedMessage, $nonceFromClient) {
    $nonce = get_transient('walletconnect_nonce_' . $address);
    if (!$nonce || $nonce !== $nonceFromClient) {
        error_log('no transient or nonce mismatch');
        return false;
    }
    $signatureValid = false;

    if(extension_loaded('gmp')) {
        // Use EthSignature to verify the signature
        $ethSignature = new EthSignature();
        if ($ethSignature->verify($nonce, $signedMessage, $address)) {
            $signatureValid = true;
        }
    } else {
        // GMP not available, use remote verification
        $response = verify_signature_remotely($nonce, $signedMessage, $address);
        if ($response['success']) {
            $signatureValid = true;
        }
    }
    if ($signatureValid) {
        // The signature is valid
        if (is_user_logged_in()) {
            // If the user is already logged in, just update their metadata
            $current_user = wp_get_current_user();
            update_user_meta($current_user->ID, 'ethereum_address', $address);
            
            // Optionally, generate and store the proxy API key
            $proxyApiKey = hash('sha256', $address . time());
            update_user_meta($current_user->ID, 'fungate_api_key', $proxyApiKey);

            if(get_option("loopring_enabled")){
                $lrc_account = get_loopring_account(get_user_meta($current_user->ID, 'ethereum_address', true));
                update_user_meta($current_user->ID,'lrc_account', $lrc_account);
                update_user_meta($current_user->ID,'nfts', get_nfts($lrc_account));
            }
            if(get_option("eth_enabled")){
                include("pro/ethereum_integration.php");
                update_user_meta($current_user->ID,'eth_nfts', get_eth_nfts($address));
            }
        } else {
            // If the user is not logged in, proceed with login or account creation
            $user_id = username_exists($address);
            if (!$user_id) {
                // User does not exist, create a new account
                $random_password = wp_generate_password();
                $user_id = wp_create_user($address, $random_password, $address . '@example.com'); // Use a dummy email
                if (!is_wp_error($user_id)) {
                    // Set the role to fungate_user
                    wp_update_user(array(
                        'ID' => $user_id,
                        'role' => 'fungate_user'
                    ));
                }
            }
            // Retrieve the user object
            $user = get_user_by('id', $user_id);
            // Log the user in
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);
            do_action('wp_login', $user->user_login, $user);

    
            // Store necessary information in user metadata
            update_user_meta($user_id, 'ethereum_address', $address);
    
            // Generate and store the proxy API key
            $proxyApiKey = hash('sha256', $address . time());
            update_user_meta($user_id, 'fungate_api_key', $proxyApiKey);

            $lrc_account = get_loopring_account(get_user_meta($user_id, 'ethereum_address', true));
            update_user_meta($user_id,'lrc_account', $lrc_account);
            update_user_meta($user_id,'nfts', get_nfts($lrc_account));
        }
        return true;
    }
    return false; // Return false if signature does not match
}

function verify_signature_remotely($nonce, $signedMessage, $address) {
    // URL of your remote endpoint
    $url = 'https://fungate.dev/wp-content/plugins/Fungate/core/license/signing-endpoint.php';

    // Retrieve the Fungate license key from WordPress options
    $fungate_license = get_option('fungate_license');

    // Data to be sent in the POST request
    $body = array(
        'nonce' => $nonce,
        'signedMessage' => $signedMessage,
        'address' => $address,
        'fungate_license' => $fungate_license
    );

    // Make the POST request
    $response = wp_remote_post($url, array(
        'body' => $body,
        'timeout' => 20 // Increase the timeout to 20 seconds
    ));
    

    // Check for errors in the request
    if (is_wp_error($response)) {
        error_log('Remote verification failed: ' . $response->get_error_message());
        return ['success' => false];
    }

    // Decode the JSON response
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Check for success in the response data
    if (!empty($data) && isset($data['success'])) {
        return ['success' => $data['success']];
    }

    // Default to failure if the response is not as expected
    return ['success' => false];
}


function get_loopring_account($ethereum_address) {
    $loopringApiKey = get_option('loopring_api_key');
    $url = "https://api3.loopring.io/api/v3/account?owner=$ethereum_address";
    $headers = ['Content-Type: application/json', "X-API-KEY: $loopringApiKey"];
    $response = curl_request($url, $headers);

    //error_log('Response: ' . $response);

    $decodedResponse = json_decode($response, true);
    if (is_array($decodedResponse) && isset($decodedResponse['accountId'])) {
        return $decodedResponse['accountId'];
    } else {
        // Handle error or invalid response
        error_log('Invalid or unexpected response format');
        return null;
    }
}


function get_nfts($loopring_account_id){
    $loopringApiKey = get_option('loopring_api_key');
    $url = "https://api3.loopring.io/api/v3/user/nft/balances?accountId=$loopring_account_id";
    $headers = ['Content-Type: application/json', "X-API-KEY: $loopringApiKey"];
    $nfts = [];
    $offset = 0;
    do {
        $url_with_offset = $url . "&offset=$offset";
        $response   = curl_request($url_with_offset, $headers);
        if($response == false){
            break;
        }
        $json = json_decode($response, true);
        $nfts = array_merge($nfts, $json['data']);
        $offset += count($json['data']);
    } while ($offset < $json['totalNum']);
    // Clean up the NFT array
    $nfts_to_save = array();
    foreach ($nfts as $nft) {
        $nfts_to_save[] = array(
            'minter' => $nft['minter'],
            'tokenAddress' => $nft['tokenAddress'],
            'nftId' => $nft['nftId'],
        );
    }
    //error_log($nfts_to_save);
    return $nfts_to_save;
}

// helper function
function curl_request($url, $headers){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode != 200) {
        return false;
    }

    return $response;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = $_POST['address'];
    $signedMessage = $_POST['signedMessage'];
    $nonceFromClient = $_POST['nonceFromClient'];

    if (handle_signed_message($address, $signedMessage, $nonceFromClient)) {
        echo "success";
    } else {
        echo $address, $signedMessage, $nonceFromClient;
    }
} else {
    $nonce = generate_transient($_GET['address']);
    echo json_encode(['nonce' => $nonce]);
}
