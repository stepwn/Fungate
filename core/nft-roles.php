<?php
defined('ABSPATH') or die('No script kiddies please!');
/**
 * Handles functionalities related to NFT-based user roles in WordPress.
 */

/**
 * Registers the 'fungate_user' role on WordPress initialization.
 */
function fungate_register_fungate_user_role() {
    add_role(
        'fungate_user',
        'Fungate User',
        array(
            'read' => true,  // Allows a user to read. Add more capabilities as needed.
        )
    );
}
add_action('init', 'fungate_register_fungate_user_role');

/**
 * Deletes NFT metadata on user login.
 *
 * @param string $user_login User login name.
 * @param WP_User $user User object.
 */
function fungate_assign_role_by_nft_on_login($user_login, $user) {
    delete_user_meta($user->ID, 'nfts');
}
add_action('wp_login', 'fungate_assign_role_by_nft_on_login', 10, 2);

/**
 * Increments the site-wide version number when the 'nft_roles' option is updated.
 *
 * @param string $option_name Name of the option.
 * @param mixed $old_value The old option value.
 * @param mixed $value The new option value.
 */
function fungate_increment_version_on_nft_role_change($option_name, $old_value, $value) {
    if ($option_name !== 'nft_roles') {
        return; // Not the option we're interested in.
    }

    $version = get_option('fungate_settings_version', 0);
    update_option('fungate_settings_version', ++$version);
}
add_action('updated_option', 'fungate_increment_version_on_nft_role_change', 10, 3);

/**
 * Stores the site-wide version number in the user's session on login.
 *
 * @param string $user_login User login name.
 * @param WP_User $user User object.
 */
function fungate_store_version_on_login($user_login, $user) {
    update_user_meta($user->ID, 'user_version', get_option('fungate_settings_version', 0));
}
add_action('wp_login', 'fungate_store_version_on_login', 10, 2);

/**
 * Checks the user's version number against the site-wide version number on each page load.
 * Logs out the user if the version numbers do not match.
 */
function fungate_check_version_on_page_load() {
    $current_user = wp_get_current_user();
    if ($current_user->ID === 0) {
        return; // Not logged in.
    }

    $user_version = get_user_meta($current_user->ID, 'user_version', true);
    if ($user_version != get_option('fungate_settings_version', 0)) {
        wp_logout();
        wp_redirect(home_url()); // Optional: Redirect to home page after logout.
        exit;
    }
}
add_action('init', 'fungate_check_version_on_page_load');
