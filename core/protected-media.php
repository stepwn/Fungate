<?php
defined('ABSPATH') or die('No script kiddies please!');
/**
 * protected-media.php
 *
 * Handles functionalities related to protecting and serving media files.
 * Located in plugins/fungate/core/
 */

/**
 * Enqueue JavaScript
 * Enqueues a script for nonce fetching and URL updating on the frontend.
 */
function fungate_enqueue_scripts() {
    wp_enqueue_script('fungate-nonce-fetcher', plugin_dir_url(__FILE__) . 'js/fungate-nonce-fetcher.js', array('jquery'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'fungate_enqueue_scripts');

/**
 * Create a Nonce
 * Creates a nonce based on the file URL.
 *
 * @param string $file_url The URL of the file.
 * @return string A nonce based on the file URL.
 */
function fungate_create_nonce($file_url) {
    return wp_create_nonce('fungate_download_' . basename($file_url));
}

/**
 * Verify Nonce
 * Verifies a nonce based on the file URL.
 *
 * @param string $nonce The nonce to verify.
 * @param string $file_url The URL of the file.
 * @return boolean|int False if the nonce is invalid, 1 if the nonce is valid and generated in the past 12 hours, 2 if the nonce is valid and generated between 12-24 hours ago.
 */
function fungate_verify_nonce($nonce, $file_url) {
    $nonce_action = 'fungate_download_' . basename($file_url);
    return wp_verify_nonce($nonce, $nonce_action);
}

/**
 * Generate Pre-Signed URL
 * Generates a pre-signed URL with an expiration time and a signature.
 *
 * @param string $file_url The URL of the file.
 * @return string Pre-signed URL.
 */
function fungate_generate_presigned_url($file_url) {
    $expiration = time() + (15); // URL is valid for 15 seconds
    $signature = hash_hmac('sha256', $file_url . '|' . $expiration, NONCE_KEY);
    return add_query_arg(array(
        'file' => urlencode($file_url),
        'expires' => $expiration,
        'signature' => $signature
    ), rest_url('fungate/v1/serve-file/'));
}

/**
 * Embed Protected Media Shortcode
 * Registers a shortcode that embeds protected media with a nonce in the URL.
 */
function fungate_embed_protected_media($atts) {
    $defaults = ['src' => '', 'text' => 'Download'];
    $atts = shortcode_atts($defaults, $atts);

    $file_url = esc_url($atts['src']);
    $button_text = esc_attr($atts['text']);
    $nonce = fungate_create_nonce($file_url);

    $api_url = esc_url(add_query_arg(['file' => urlencode($file_url), 'nonce' => $nonce], rest_url('fungate/v1/serve-file/')));

    return "<a href='{$api_url}' download><button>{$button_text}</button></a>";
}
add_shortcode('fungate_media_download', 'fungate_embed_protected_media');

/**
 * Serve Protected File
 * Serves the protected file after validating the signature and expiration.
 */
function fungate_serve_protected_file(WP_REST_Request $request) {
    $file_url = sanitize_text_field($request->get_param('file'));
    $expires = sanitize_text_field($request->get_param('expires'));
    $signature = sanitize_text_field($request->get_param('signature'));

    // Validate the signature
    $valid_signature = hash_hmac('sha256', $file_url . '|' . $expires, NONCE_KEY);
    if (time() > $expires || !hash_equals($valid_signature, $signature)) {
        return new WP_Error('unauthorized', 'Unauthorized access or expired link', ['status' => 403]);
    }

    $file_path = WP_PLUGIN_DIR . '/fungate/protected-folder/' . basename($file_url);
    if (!file_exists($file_path) || !is_readable($file_path)) {
        return new WP_Error('file_not_found', 'File not found', ['status' => 404]);
    }

    // Serve the file securely
    header('Content-Type: ' . mime_content_type($file_path));
    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
    readfile($file_path);
    exit;
}

// Register REST route for serving files
add_action('rest_api_init', function () {
    register_rest_route('fungate/v1', '/serve-file/', [
        'methods' => 'GET',
        'callback' => 'fungate_serve_protected_file',
        'permission_callback' => '__return_true'
    ]);
});

/**
 * Embed Media into Posts Shortcode
 * Registers a shortcode that embeds media into posts with a data attribute for the base API URL.
 */
function fungate_stream_media_shortcode($atts) {
    $defaults = array(
        'type' => 'image', // Default type is image.
        'src' => ''
    );
    $atts = shortcode_atts($defaults, $atts);

    $file_url = esc_url_raw($atts['src']);
    $user_access_key = 'fungate_access_' . wp_generate_uuid4();
    set_transient($user_access_key, 'valid', 5 * MINUTE_IN_SECONDS);
    $base_api_url = esc_url(fungate_generate_presigned_url($file_url));

    // Render the media based on its type.
    $output = '';
    switch ($atts['type']) {
        case 'video':
            $output = '<video controls width="100%" data-base-url="' . $base_api_url . '"><source type="video/mp4">Your browser does not support the video tag.</video>';
            break;
        case 'audio':
            $output = '<audio controls data-base-url="' . $base_api_url . '"><source type="audio/mp3">Your browser does not support the audio element.</audio>';
            break;
        case 'image':
            $output = '<img data-base-url="' . $base_api_url . '" alt="Protected Image" width="100%">';
            break;
    }

    return $output;
}

add_shortcode('fungate_media', 'fungate_stream_media_shortcode');

/**
 * REST Endpoint for fetching a fresh nonce
 * Provides a REST endpoint to fetch a fresh nonce for users who have valid access.
 */
add_action('rest_api_init', function () {
    register_rest_route('fungate/v1', '/nonce', array(
        'methods' => 'GET',
        'callback' => function(WP_REST_Request $request) {
            $access_key = sanitize_text_field($request->get_param('access_key'));
            if (get_transient($access_key) === 'valid') {
                delete_transient($access_key);
                return new WP_REST_Response(wp_create_nonce('fungate_download_action'), 200);
            } else {
                return new WP_Error('unauthorized', 'Unauthorized access', ['status' => 401]);
            }
        },
        'permission_callback' => '__return_true'
    ));
});

/**
 * Lists media files from the protected folder.
 * Provides a REST endpoint to list media files from a protected directory.
 *
 * @return WP_REST_Response List of media file URLs or an error message.
 */
function fungate_list_media() {
    $protected_folder_path = WP_PLUGIN_DIR . '/fungate/protected-folder/';
    $protected_folder_url = plugins_url('/protected-folder/', __FILE__);

    if (!file_exists($protected_folder_path) || !is_readable($protected_folder_path)) {
        return new WP_REST_Response('Directory not found or not readable', 404);
    }

    $files = scandir($protected_folder_path);
    if ($files === false) {
        return new WP_REST_Response('Error reading directory', 500);
    }

    $file_urls = array();
    foreach ($files as $file) {
        if ($file !== '..' && $file !== '.' && $file !== '.htaccess') {
            $file_urls[] = esc_url($protected_folder_url . $file);
        }
    }

    return new WP_REST_Response($file_urls, 200);
}
add_action('rest_api_init', function () {
    register_rest_route('fungate/v1', '/list-media', array(
        'methods' => 'GET',
        'callback' => 'fungate_list_media',
        'permission_callback' => function() {
            return current_user_can('upload_files');
        }
    ));
});
