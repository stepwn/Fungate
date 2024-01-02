<?php

defined('ABSPATH') or die('No script kiddies please!');

function fungate_enqueue_admin_styles() {
    wp_enqueue_style('fungate-admin-styles', plugins_url('../core/css/fungate-admin.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'fungate_enqueue_admin_styles');


//plugin settings header
function fungate_admin_page_render() {
    ?>
    <div class="fungate-help-header">
        <h2>Thanks For Using Fungate!</h2>
        <a href="https://tawk.to/fungate" target="_blank" class="fungate-help-link">Need help? Click here for real-time support</a>
    </div>
    <?php
}


add_action('admin_menu', 'fungate_plugin_menu');
function fungate_plugin_menu() {
    $icon_url = plugins_url('../core/media/fungate_logo.png', __FILE__);

    // Add the Fungate top-level menu
    add_menu_page('Fungate Plugin', 'Fungate', 'manage_options', 'fungate', 'fungate_menu_page', $icon_url);

    // Add the Posts submenu under Fungate
    //add_submenu_page('fungate', 'Fungate NFTs', 'Posts', 'manage_options', 'edit.php?post_type=fungate_nft');

     // Add the Fungate Media submenu under Fungate
     add_submenu_page('fungate', 'Fungate Media', 'Fungate Media', 'manage_options', 'fungate-media', 'fungate_media_page');

     // Add the Settings submenu under Fungate
    add_submenu_page('fungate', 'Roles', 'Roles', 'manage_options', 'fungate-roles-settings', 'fungate_roles_page');

    // Add the Settings submenu under Fungate
    add_submenu_page('fungate', 'Fungate Settings', 'Settings', 'manage_options', 'fungate-settings', 'fungate_settings_page');

    // Remove the duplicate sub menu item
    remove_submenu_page('fungate', 'Fungate');
}





// fungate Settings
add_action('admin_init', 'fungate_settings_init');
function fungate_settings_init() {
    register_setting('fungate_settings', 'loopring_api_key');
	register_setting('fungate_settings', 'wc_projectId');
    register_setting('fungate_settings', 'fungate_unlock_button_css', ['default' => ""]);
    register_setting('fungate_settings', 'nft_roles');
    register_setting('fungate_settings', 'fungate_default_fail_message', ['default' => "<span class='dashicons dashicons-lock' style='font-size: 2.5em; width: 2.5em; display: block; margin: auto; box-sizing: border-box;'></span><br><b>You do not own the required NFT to view this content.</b><br><small>If you recently acquired the NFT, it may take up to 30 minutes for the transaction to post and be available.</small>"]);
    register_setting('fungate_settings', 'fungate_add_accountButton_to_fail', ['default' => 0]);
    register_setting('fungate_settings', 'nft_roles_enabled', ['default' => 0]);
    register_setting('fungate_settings', 'fungate_nft_enabled', ['default' => 1]);
    register_setting('fungate_settings', 'fungate_buddypress_enabled', ['default' => 0]);
    register_setting('fungate_settings', 'fungate_woocommerce_enabled', ['default' => 0]);
    register_setting('fungate_settings', 'fungate_tinymce_enabled', ['default' => 0]);
    register_setting('fungate_settings', 'fungate_license', ['default' => "FREE FOREVER"]);
    register_setting('fungate_settings', 'default_nft_role', ['default' => get_option('default_role')]);
}

include_once('menu-page.php');
include_once('settings-page.php');
include_once('protected-media-settings-page.php');
include_once('roles-settings-page.php');