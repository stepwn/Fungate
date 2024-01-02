<?php
defined('ABSPATH') or die('No script kiddies please!');
/**
 * Register REST API endpoints.
 */
add_action('rest_api_init', 'fungate_register_logout_rest_route');

/**
 * Register the logout endpoint.
 */
function fungate_register_logout_rest_route() {
    register_rest_route('fungate/v1', '/logout', array(
        'methods' => 'POST',
        'callback' => 'fungate_rest_logout',
        'permission_callback' => function () {
            //return is_user_logged_in();
            return true;
        }
    ));
}

/**
 * REST API callback for logging out the user.
 *
 * @return WP_REST_Response REST response with logout status.
 */
function fungate_rest_logout(WP_REST_Request $request) {
    // Check if the current user is not an administrator.
    if (!current_user_can('administrator')) {
        wp_logout();
        return new WP_REST_Response('User logged out successfully.', 200);
    } else {
        return new WP_REST_Response('Administrator cannot be logged out this way.', 403);
    }
}
