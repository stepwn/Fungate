<?php
function walletconnect_enqueue_scripts() {
    // Enqueue plugin script
    wp_enqueue_script(
        'walletconnect-wordpress', 
        plugin_dir_url(__FILE__) . '/js/walletconnect.js?v=' . time(), 
        array('jquery'), 
        null, 
        true
    );

    // Add the type attribute as 'module'
    wp_script_add_data('walletconnect-wordpress', 'type', 'module');

    // Enqueue style
    wp_enqueue_style(
        'fungate-style', 
        plugin_dir_url(__FILE__) . '/css/style.css', 
        array(), 
        filemtime(plugin_dir_path(__FILE__) . '/css/style.css'), 
        'all'
    );

    // Enqueue Dashicons
    wp_enqueue_style('dashicons');

    // Get the current user's ID
    $current_user_id = get_current_user_id();
     // Retrieve the proxy key from user meta
     $proxy_key = get_user_meta($current_user_id, 'fungate_api_key', true);

     // Retrieve the Ethereum address from user meta
     $eth_address = get_user_meta($current_user_id, 'ethereum_address', true);
 
     // Retrieve the Loopring account or any other data you stored
     $lrc_account = get_user_meta($current_user_id, 'lrc_account', true);

    // Retrieve and escape the WalletConnect project ID
    $wc_projectId = esc_attr(get_option('wc_projectId'));

    // Get plugin directory URL
    $plugin_dir = esc_url(plugins_url('', __FILE__));

    // Prepare inline script
    $inline_script = sprintf(
    "const fungateKey = '%s'; const wc_projectId = '%s'; const plugin_dir = '%s'; const lrc_account_id = '%s'; const eth_address = '%s';",
    esc_js($proxy_key), esc_js($wc_projectId), esc_js($plugin_dir), esc_js($lrc_account), esc_js($eth_address)
);


    // Add inline script to the WalletConnect script
    wp_add_inline_script('walletconnect-wordpress', $inline_script);

    // Enqueue the script
    wp_enqueue_script('walletconnect-wordpress');
}
add_action('wp_enqueue_scripts', 'walletconnect_enqueue_scripts');
