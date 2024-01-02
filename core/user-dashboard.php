<?php
defined('ABSPATH') or die('No script kiddies please!');
/**
 * Adds custom fields to the user profile page in the admin area.
 */
add_action('show_user_profile', 'fungate_custom_user_profile_fields');
add_action('edit_user_profile', 'fungate_custom_user_profile_fields');

/**
 * Displays custom fields on the user profile page.
 *
 * @param WP_User $user User object for the current profile.
 */
function fungate_custom_user_profile_fields($user) {
    // Retrieve the Ethereum address and NFTs for the user
    $ethereum_address = esc_html(get_user_meta($user->ID, 'ethereum_address', true));
    $eth_nfts = get_user_meta($user->ID, 'eth_nfts', true);

    // Initialize an empty string for NFTs display
    $nfts_string = '';

    // Check if the user has any NFTs and format them for display
    if (is_array($eth_nfts) && !empty($eth_nfts)) {
        foreach ($eth_nfts as $nft) {
            // Customize this line to format the NFTs however you like
            $nfts_string .= '<div style="border: 2px solid black;">' . print_r($nft, true) . '</div>';
        }
    } else {
        $nfts_string = '<p>No NFTs found.</p>';
    }

    // Output custom fields
    echo '<h3>Extra Profile Information</h3>';
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th><label for="address">Ethereum Address</label></th>';
    echo '<td>' . $ethereum_address . '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th><label for="eth_nfts">ETH NFTs</label></th>';
    echo '<td>' . esc_html($nfts_string) . '</td>';
    echo '</tr>';
    echo '</table>';
}
