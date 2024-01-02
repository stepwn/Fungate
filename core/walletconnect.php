<?php
defined('ABSPATH') or die('No script kiddies please!');
/**
 * Enqueues scripts and styles for WalletConnect functionality.
 */
function fungate_walletconnect_enqueue_scripts() {
    // Enqueue WalletConnect script with cache busting
    wp_enqueue_script(
        'walletconnect-wordpress', 
        plugin_dir_url(__FILE__) . '/js/walletconnect.js',
        array('jquery'), 
        filemtime(plugin_dir_path(__FILE__) . '/js/walletconnect.js'), 
        true
    );

    // Set script type to 'module'
    wp_script_add_data('walletconnect-wordpress', 'type', 'module');

    // Enqueue custom style with version based on file modification time
    wp_enqueue_style(
        'fungate-style', 
        plugin_dir_url(__FILE__) . '/css/style.css', 
        array(), 
        filemtime(plugin_dir_path(__FILE__) . '/css/style.css'), 
        'all'
    );

    // Enqueue Dashicons for use in the plugin
    wp_enqueue_style('dashicons');

    // Get the current user ID and retrieve relevant metadata
    $current_user_id = get_current_user_id();
    $proxy_key = get_user_meta($current_user_id, 'fungate_api_key', true);
    $eth_address = get_user_meta($current_user_id, 'ethereum_address', true);
    $lrc_account = get_user_meta($current_user_id, 'lrc_account', true);

    // Prepare and escape WalletConnect project ID and other variables
    $wc_projectId = esc_js(get_option('wc_projectId'));
    $plugin_dir = esc_js(plugins_url('', __FILE__));

    // Create inline script with necessary variables
    $inline_script = sprintf(
        "const fungateKey = '%s'; const wc_projectId = '%s'; const plugin_dir = '%s'; const lrc_account_id = '%s'; const eth_address = '%s';",
        $proxy_key, $wc_projectId, $plugin_dir, $lrc_account, $eth_address
    );

    // Add inline script to the WalletConnect script
    wp_add_inline_script('walletconnect-wordpress', $inline_script);
}
add_action('wp_enqueue_scripts', 'fungate_walletconnect_enqueue_scripts');
