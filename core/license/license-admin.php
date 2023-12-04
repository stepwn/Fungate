<?php
require_once('license-registration.php');
// Add menu item to the admin dashboard
function looppress_add_admin_menu() {
    add_menu_page('Fungate Licenses', 'Fungate Licenses', 'manage_options', 'looppress_licenses', 'looppress_license_page');
}
add_action('admin_menu', 'looppress_add_admin_menu');

// Display licenses and add new license form
function looppress_license_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'looppress_licenses';

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && current_user_can('manage_options')) {
        insert_new_license($_POST['website'], $_POST['product_id']);
        wp_redirect($_SERVER['REQUEST_URI']);
        exit;
    }

    // Fetch existing licenses
    $licenses = $wpdb->get_results("SELECT * FROM {$table_name}");

    ?>
    <div class="wrap">
        <h1>Fungate Licenses</h1>

        <h2>Add New License</h2>
        <form method="post">
            <?php wp_nonce_field('add_new_license'); ?>
            <input type="text" name="website" placeholder="Website" required>
            <input type="text" name="product_id" placeholder="Product ID" required>
            <input type="submit" value="Add License">
        </form>

        <h2>Existing Licenses</h2>
        <table>
            <tr>
                <th>License Key</th>
                <th>Website</th>
                <th>Product ID</th>
                <th>Status</th>
                <th>Last Request</th>
                <th>Requests last 24hrs</th>
                <th>Total Requests</th>
                <!-- Add more columns as needed -->
            </tr>
            <?php foreach ($licenses as $license): ?>
                <tr>
                    <td><?php echo esc_html($license->license_key); ?></td>
                    <td><?php echo esc_html($license->website); ?></td>
                    <td><?php echo esc_html($license->product_id); ?></td>
                    <td><?php echo esc_html($license->status); ?></td>
                    <td><?php echo esc_html($license->last_request_time); ?></td>
                    <td><?php echo esc_html($license->sign_requests_last_24hrs); ?></td>
                    <td><?php echo esc_html($license->total_sign_requests); ?></td>
                    <!-- Display more data as needed -->
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php
}
