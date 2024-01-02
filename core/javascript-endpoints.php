<?php
defined('ABSPATH') or die('No script kiddies please!');
/**
 * Registers REST API routes for the Fungate plugin.
 */
function fungate_register_js_rest_routes() {
    register_rest_route('fungate/v1', '/get-user-nfts', array(
        'methods' => 'GET',
        'callback' => 'fungate_get_user_nfts',
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ));
}
add_action('rest_api_init', 'fungate_register_js_rest_routes');

/**
 * Retrieves the NFTs for the current user.
 *
 * @return WP_REST_Response The response object.
 */
function fungate_get_user_nfts() {
    $user_id = get_current_user_id();
    
    // Retrieve the stored NFTs from user meta
    $nfts = get_user_meta($user_id, 'nfts', true);
    
    if ($nfts) {
        return new WP_REST_Response($nfts, 200);
    } else {
        return new WP_Error('no_nfts', 'No NFTs found for the user', array('status' => 404));
    }
}
