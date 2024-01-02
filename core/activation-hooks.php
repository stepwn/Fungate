<?php
defined('ABSPATH') or die('No script kiddies please!');
/**
 * Checks the server type during plugin activation.
 */
function fungate_check_server_type() {
    if (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') === false) {
        add_action('admin_notices', 'fungate_nginx_notice');
    }
}

/**
 * Displays an admin notice for non-Apache servers.
 */
function fungate_nginx_notice() {
    ?>
    <div class="notice notice-warning">
        <p><strong>Fungate Notice:</strong> It appears your server is not running Apache. If you are using Nginx or another non-Apache server, please manually configure your server to restrict access to the Fungate protected folder. Refer to the plugin documentation for more details.</p>
    </div>
    <?php
}

register_activation_hook(__FILE__, 'fungate_check_server_type');

add_action('after_setup_theme', 'fungate_disable_admin_bar_for_role');

/**
 * Disables the WordPress admin bar for users with the 'fungate_user' role.
 */
function fungate_disable_admin_bar_for_role() {
    $role_to_disable = 'fungate_user';
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        if (in_array($role_to_disable, $current_user->roles)) {
            add_filter('show_admin_bar', '__return_false');
        }
    }
}

