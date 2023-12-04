<?php
// login hook to use NFT roles
function assign_role_by_nft_on_login($user_login, $user) {
    delete_user_meta($user->ID, 'nfts');
}

// register default fungate_user role
add_action( 'init', 'register_fungate_user_role' );

function register_fungate_user_role() {
    add_role(
        'fungate_user',
        'Fungate User',
        array(
            'read' => true,  // Allows a user to read
            // Add more capabilities as needed
        )
    );
}

// Hook into the 'wp_login' action
add_action('wp_login', 'assign_role_by_nft_on_login', 10, 2);

// Increment the site-wide version number when the 'nft_roles' option is updated
function increment_version_on_nft_role_change($option_name, $old_value, $value) {
    if ('nft_roles' !== $option_name) {
        return; // Not the option we're interested in
    }
    $version = get_option('fungate_settings_version', 0);
    update_option('fungate_settings_version', $version + 1);
}
add_action('updated_option', 'increment_version_on_nft_role_change', 10, 3);

// Store the site-wide version number in user's session when they log in
function store_version_on_login($user_login, $user) {
    update_user_meta($user->ID, 'user_version', get_option('fungate_settings_version', 0));
}
add_action('wp_login', 'store_version_on_login', 10, 2);

// Compare stored version number to current site-wide version number on each page load
function check_version_on_page_load() {
    $current_user = wp_get_current_user();
    if (0 == $current_user->ID) return; // Not logged in
    $user_version = get_user_meta($current_user->ID, 'user_version', true);
    if ($user_version != get_option('fungate_settings_version', 0)) {
        wp_logout();
    }
}
add_action('init', 'check_version_on_page_load');