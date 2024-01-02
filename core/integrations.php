<?php
defined('ABSPATH') or die('No script kiddies please!');
add_action('plugins_loaded', 'fungate_include_integrations');

function fungate_include_integrations() {
    $integrations = array(
        'fungate_buddypress_enabled' => 'integrations/buddypress_integration.php',
        'fungate_woocommerce_enabled' => 'integrations/woocommerce_integration.php',
        //'fungate_tinymce_enabled' => 'integrations/tinymce.php',
    );

    foreach ($integrations as $option => $path) {
        if (get_option($option, 0) === '1') {
            $file = plugin_dir_path(__FILE__) . $path;
            if (file_exists($file)) {
                include_once($file);
            }
        }
    }
}
