<?php
require_once('../lib/EthSignature.php');
require_once('/home/fungate/public_html/wp-load.php' );
// Create an instance of EthSignature
$ethSignature = new EthSignature();

// Retrieve data from POST request
$nonce = $_POST['nonce'] ?? '';
$signedMessage = $_POST['signedMessage'] ?? '';
$address = $_POST['address'] ?? '';
$license = $_POST['looppress_license'] ?? '';

// Initialize response array
$response = ['success' => false, 'message' => 'Verification failed'];

function check_looppress_license($license) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'looppress_licenses';

    // Sanitize the license key
    $license = sanitize_text_field($license);

    // Query the database for the license
    $license_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE license_key = %s", $license));

    if ($license_data && $license_data->status == 'active') {
        // Increment the total sign request counter
        $wpdb->query($wpdb->prepare("UPDATE $table_name SET total_sign_requests = total_sign_requests + 1 WHERE license_key = %s", $license));

        // Handle the rolling 24-hour counter
        update_rolling_counter($license, $table_name, $license_data);

        return true; // License is valid and active
    }

    return false; // License is invalid or inactive
}

function update_rolling_counter($license, $table_name, $license_data) {
    global $wpdb;

    // Check if the last request was more than 24 hours ago
    $last_request_time = strtotime($license_data->last_request_time);
    if (time() - $last_request_time > 24 * HOUR_IN_SECONDS) {
        // Reset the counter and update the last request time
        $wpdb->update($table_name, 
            array(
                'sign_requests_last_24hrs' => 1, 
                'last_request_time' => current_time('mysql')
            ), 
            array('license_key' => $license)
        );
    } else {
        // Just increment the 24-hour counter
        $wpdb->query($wpdb->prepare("UPDATE $table_name SET sign_requests_last_24hrs = sign_requests_last_24hrs + 1 WHERE license_key = %s", $license));
    }
}


// Verify the signature
if (!empty($nonce) && !empty($signedMessage) && !empty($address) && check_looppress_license($license)) {
    if ($ethSignature->verify($nonce, $signedMessage, $address)) {
        $response = ['success' => true, 'message' => 'Verification successful'];
    }
}

// Set header to JSON for proper response format
header('Content-Type: application/json');

// Echo response as JSON
echo json_encode($response);

