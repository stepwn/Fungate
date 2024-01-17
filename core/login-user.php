<?php
defined('ABSPATH') or die('No script kiddies please!');
/**
 * Handles user login and integration with blockchain networks.
 */

require_once(__DIR__ . '/lib/FungateEthSignature.php');

add_action('rest_api_init', function () {
    register_rest_route('fungate/v1', '/login', array(
        'methods' => 'POST',
        'callback' => 'fungate_handle_rest_login',
        'permission_callback' => '__return_true'
    ));

    register_rest_route('fungate/v1', '/generate-nonce', array(
        'methods' => 'GET',
        'callback' => 'fungate_generate_rest_transient',
        'permission_callback' => '__return_true'
    ));
});

/**
 * REST API handler for user login.
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response The response object.
 */
function fungate_handle_rest_login($request) {
    $address = sanitize_text_field($request['address']);
    $signedMessage = sanitize_text_field($request['signedMessage']);
    $nonceFromClient = sanitize_text_field($request['nonceFromClient']);

    $result = fungate_handle_signed_message($address, $signedMessage, $nonceFromClient);

    return rest_ensure_response($result);
}

/**
 * Generates a transient nonce for a given address and returns via REST API.
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response The response object.
 */
function fungate_generate_rest_transient($request) {
    $address = sanitize_text_field($request['address']);
    $nonce = bin2hex(random_bytes(16));

    if (set_transient('walletconnect_nonce_' . $address, $nonce, 60 * 5)) {
        return rest_ensure_response($nonce);
    } else {
        return rest_ensure_response(array('error' => 'Nonce generation failed.'));
    }
}

/**
 * Handles the verification of the signed message.
 *
 * @param string $address Ethereum address.
 * @param string $signedMessage Signed message.
 * @param string $nonceFromClient Nonce received from the client.
 * @return bool True if verification is successful, false otherwise.
 */
function fungate_handle_signed_message($address, $signedMessage, $nonceFromClient) {
    $nonce = get_transient('walletconnect_nonce_' . $address);
    if (!$nonce || $nonce !== $nonceFromClient) {
        error_log('no transient or nonce mismatch');
        return false;
    }
    $signatureValid = false;

    if(extension_loaded('gmp')) {
        // Use FungateEthSignature to verify the signature
        $ethSignature = new FungateEthSignature();
        if ($ethSignature->verify($nonceFromClient, $signedMessage, $address)) {
            $signatureValid = true;
        }
    } else {
        // GMP not available, use remote verification
        $response = fungate_verify_signature_remotely($nonceFromClient, $signedMessage, $address);
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

        } else {
            // If the user is not logged in, proceed with login or account creation
            $user_query = new WP_User_Query(array(
                'meta_key'   => 'ethereum_address',
                'meta_value' => $address
            ));
    
            $users = $user_query->get_results();
    
            if (!empty($users)) {
                // User with the Ethereum address found
                $user = $users[0]; // Assuming the first user (should be unique)
                $user_id = $user->ID;
            } else {
                // User does not exist, create a new account
                $random_password = wp_generate_password();
                $user_id = wp_create_user($address, $random_password, $address . '@example.com'); // Use a dummy email
                if (!is_wp_error($user_id)) {
                    // Set the role to fungate_user
                    wp_update_user(array(
                        'ID' => $user_id,
                        'role' => 'fungate_user'
                    ));
            
                    // Store Ethereum address in user metadata
                    update_user_meta($user_id, 'ethereum_address', $address);
                }
            }
            
            // Ensure the user object is retrieved after creation
            $user = get_user_by('id', $user_id);
            if ($user) {
                // Log the user in
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
                do_action('wp_login', $user->user_login, $user);
            }
    
            // Generate and store the proxy API key
            $proxyApiKey = hash('sha256', $address . time());
            update_user_meta($user_id, 'fungate_api_key', $proxyApiKey);
            
        }
        fungate_update_user($address);
        return true;
    }
    return false; // Return false if signature does not match
}

/**
 * Verifies the signature remotely.
 *
 * @param string $nonce Nonce value.
 * @param string $signedMessage Signed message.
 * @param string $address Ethereum address.
 * @return array Response array with success status.
 */
function fungate_verify_signature_remotely($nonce, $signedMessage, $address) {
    // URL of your remote endpoint
    $url = 'https://fungate.io/wp-content/plugins/fungate/core/license/signing-endpoint.php';

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

/**
 * Updates the user metadata with NFT data.
 *
 * @param string $address Ethereum address.
 */
function fungate_update_user($address) {
    // Retrieve the user ID based on the Ethereum address
    $user_query = new WP_User_Query(array(
        'meta_key'   => 'ethereum_address',
        'meta_value' => $address
    ));
    $users = $user_query->get_results();

    if (empty($users)) {
        // User not found, return early
        return;
    }

    $user_id = $users[0]->ID;

    // Update user metadata with NFT data
    if(get_option("loopring_enabled")){
        $lrc_account = fungate_get_loopring_account(get_user_meta($user_id, 'ethereum_address', true));
        update_user_meta($user_id,'lrc_account', $lrc_account);
        update_user_meta($user_id,'nfts', fungate_get_nfts($lrc_account));
    }
    if(get_option("ethereum_enabled")){
        include("chainhopper/ethereum_integration.php");
        update_user_meta($current_user->ID,'eth_nfts', get_eth_nfts($address));
    }
    if(get_option("arbitrum_enabled")){
        include("chainhopper/arbitrum_integration.php");
        update_user_meta($current_user->ID,'arbitrum_nfts', get_arbitrum_nfts($address));
    }
    if (get_option("starknet_enabled")) {
        include("chainhopper/starknet_integration.php");
        update_user_meta($current_user->ID, 'starknet_nfts', get_starknet_nfts($address));
    }
    
    if (get_option("polygon_enabled")) {
        include("chainhopper/polygon_integration.php");
        update_user_meta($current_user->ID, 'polygon_nfts', get_polygon_nfts($address));
    }
    
    if (get_option("optimism_enabled")) {
        include("chainhopper/optimism_integration.php");
        update_user_meta($current_user->ID, 'optimism_nfts', get_optimism_nfts($address));
    }
}


/**
 * Retrieves the Loopring account ID for a given Ethereum address.
 *
 * @param string $ethereum_address The Ethereum address.
 * @return mixed The Loopring account ID or null if not found or on error.
 */
function fungate_get_loopring_account($ethereum_address) {
    $loopringApiKey = get_option('loopring_api_key');
    $url = "https://api3.loopring.io/api/v3/account?owner=" . urlencode($ethereum_address);
    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-API-KEY' => $loopringApiKey
        )
    );

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        error_log('Error fetching Loopring account: ' . $response->get_error_message());
        return null;
    }

    $body = wp_remote_retrieve_body($response);
    $decodedResponse = json_decode($body, true);

    if (isset($decodedResponse['accountId'])) {
        return $decodedResponse['accountId'];
    }

    error_log('Invalid or unexpected response format for Loopring account');
    return null;
}

/**
 * Retrieves NFTs for a given Loopring account ID.
 *
 * @param int $loopring_account_id The Loopring account ID.
 * @return array List of NFTs.
 */
function fungate_get_nfts($loopring_account_id) {
    $loopringApiKey = get_option('loopring_api_key');
    $url_base = "https://api3.loopring.io/api/v3/user/nft/balances?accountId=" . urlencode($loopring_account_id);
    $nfts = [];
    $offset = 0;

    do {
        $url = $url_base . "&offset=" . $offset;
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-API-KEY' => $loopringApiKey
            )
        );
        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            error_log('Error fetching NFTs: ' . $response->get_error_message());
            break;
        }

        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body, true);

        if (!isset($json['data'])) {
            break;
        }

        $nfts = array_merge($nfts, $json['data']);
        $offset += count($json['data']);
    } while ($offset < $json['totalNum']);

    return array_map(function ($nft) {
        return array(
            'minter' => $nft['minter'],
            'tokenAddress' => $nft['tokenAddress'],
            'nftId' => $nft['nftId'],
        );
    }, $nfts);
}