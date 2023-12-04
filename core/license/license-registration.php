<?php
require_once('/home/fungate/public_html/wp-load.php' );
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

function insert_new_license($website, $product_id) {
    // Check if the current user has the 'manage_options' capability
    if (!current_user_can('manage_options')) {
        return ['success' => false, 'error' => 'Insufficient permissions'];
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'looppress_licenses';
    // Create the table if it doesn't exist
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        create_looppress_licenses_table($table_name);
    }
    // Sanitize inputs
    $website = sanitize_text_field($website);
    $product_id = sanitize_text_field($product_id);

    // Generate and check the license key
    $license_key = '';
    $max_attempts = 5; // limit the number of attempts to avoid an endless loop
    for ($attempts = 0; $attempts < $max_attempts; $attempts++) {
        $license_key = generate_license_key();
        $existing = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE license_key = %s", $license_key));

        if ($existing == 0) {
            break; // Unique key found
        }
    }

    if ($existing != 0) {
        return ['success' => false, 'error' => 'Failed to generate a unique license key'];
    }

    // Attempt to insert the new license
    $result = $wpdb->insert(
        $table_name,
        array(
            'license_key' => $license_key,
            'website' => $website,
            'product_id' => $product_id,
            'activation_date' => current_time('mysql'),
            'status' => 'active'
        )
    );

    if ($result === false) {
        return ['success' => false, 'error' => 'Database insertion failed'];
    }

    return ['success' => true, 'license_key' => $license_key];
}

function generate_license_key() {
    // Generates a 12 character random string
    return wp_generate_password(24, false); 
}

function create_looppress_licenses_table($table_name) {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        license_key varchar(255) NOT NULL,
        website varchar(255) NOT NULL,
        product_id varchar(255) NOT NULL,
        total_sign_requests INT DEFAULT 0,
        sign_requests_last_24hrs INT DEFAULT 0,
        activation_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        last_request_time DATETIME DEFAULT '0000-00-00 00:00:00',
        status varchar(50) DEFAULT 'inactive' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    dbDelta($sql);
}