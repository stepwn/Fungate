<?php
defined('ABSPATH') or die('No script kiddies please!');
function fungate_enqueue_media_upload_scripts($hook) {
    if ('settings_page_fungate-media' === $hook) {
        wp_enqueue_script('fungate-media-upload', plugin_dir_url(__FILE__) . 'js/fungate-media.js', array('jquery'), '1.0.0', true);

        // Localize the script with new data
        $script_data = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_rest'),
            'upload_url' => rest_url('fungate/v1/upload-media'),
            'delete_url' => rest_url('fungate/v1/delete-media')
        );
        wp_localize_script('fungate-media-upload', 'fungateData', $script_data);
    }
}

add_action('admin_enqueue_scripts', 'fungate_enqueue_media_upload_scripts');



/**
 * Displays a media management page in the admin area.
 */
function fungate_media_page() {
    fungate_admin_page_render();
    if (!current_user_can('upload_files')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Display media upload form
    echo '<div class="fungate-wrap">';
    echo '<h1>Fungate Media</h1>';
    echo '<p>Welcome to the Fungate Media dashboard. Here, you can easily upload and manage your media files.</p>';
    
    echo '<h2>Upload Media</h2>';
    echo '<form id="fungate-media-upload-form" enctype="multipart/form-data">';
    echo '<input type="file" name="file">';
    echo '<input type="submit" value="Upload" class="button button-primary">';
    echo '</form>';

    echo '<h2>Uploaded Files</h2>';
    
    $protected_folder_path = WP_PLUGIN_DIR . '/fungate/protected-folder/';


    $files = scandir($protected_folder_path);
    
    if (!empty($files)) {
        echo '<ul>';
        // Base URL of your WordPress site
        $base_url = site_url();

        // Absolute path to the WordPress directory
        $wp_dir = ABSPATH;

        // Replace the absolute server path portion with the site URL to get a full URL
        $relative_folder_path = str_replace($wp_dir, '', $protected_folder_path);

        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && $file != '.htaccess') {
                // Construct the full URL to the file
                $file_url = $base_url . '/' . $relative_folder_path . urlencode($file);
                echo '<li>' . $file . ' - <button class="copy-link-button" data-url="' . $file_url . '">Copy Link</button> - <button class="delete-button" data-file="' . urlencode($file) . '">Delete</button></li>';
            }
        }

        
        echo '</ul>';
    } else {
        echo '<p>No files uploaded.</p>';
    }
    
    echo '</div>'; // End of wrap div
}


add_action( 'rest_api_init', function () {
    register_rest_route( 'fungate/v1', '/upload-media', array(
        'methods' => 'POST',
        'callback' => 'fungate_handle_media_upload',
        'permission_callback' => function () {
            return current_user_can( 'upload_files' );
        },
    ));

    register_rest_route( 'fungate/v1', '/delete-media', array(
        'methods' => 'POST',
        'callback' => 'fungate_handle_media_delete',
        'permission_callback' => function () {
            return current_user_can( 'upload_files' );
        },
    ));
});

function fungate_handle_media_upload(WP_REST_Request $request) {
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    header('Content-Type: application/json');
    $protected_folder_path = WP_PLUGIN_DIR . '/fungate/protected-folder/';
    $protected_folder_url = plugin_dir_url(__FILE__) . '../../fungate/protected-folder/'; // Define this correctly


    // Check and create the protected folder and .htaccess file if they don't exist
    if (!file_exists($protected_folder_path)) {
        if (!mkdir($protected_folder_path, 0755, true)) {
            error_log('Failed to create protected folder');
            return new WP_REST_Response('Server Error: Unable to create protected folder.', 500);
        }

        // Define the path to the .htaccess file
        $htaccess_path = $protected_folder_path . '.htaccess';

        // Define the rules to write to the .htaccess file
        $htaccess_rules = "Order deny,allow" . PHP_EOL . "Deny from all";

        // Write the rules to the .htaccess file
        if (!file_put_contents($htaccess_path, $htaccess_rules)) {
            error_log('Failed to create .htaccess file');
            return new WP_REST_Response('Server Error: Unable to create .htaccess file.', 500);
        }
    }

    // Check if the protected folder exists and is writable
    if (!is_writable($protected_folder_path)) {
        error_log('Protected folder is not writable');
        return new WP_REST_Response('Server Error: Protected folder is not writable.', 500);
    }

    // Handle the file upload
    $file = $request->get_file_params()['file'];
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        error_log('File upload error: ' . $file['error']);
        return new WP_REST_Response('Error uploading file: ' . $file['error'], 500);
    }


     // Handle the file upload
     $upload_overrides = array('test_form' => false);
     $uploaded_file_info = wp_handle_upload($file, $upload_overrides);
 
     if (isset($uploaded_file_info['error'])) {
         error_log('Error in uploading file: ' . $uploaded_file_info['error']);
         return new WP_REST_Response('Error in uploading file: ' . $uploaded_file_info['error'], 500);
     }
     // Sanitize the file name for consistency
    $sanitized_file_name = fungate_sanitize_file_name(basename($uploaded_file_info['file']));

 
    // Move the file to your protected folder
    $new_file_path = $protected_folder_path . $sanitized_file_name;
    $new_file_url = $protected_folder_url . $sanitized_file_name; // Ensure this is defined correctly

     if (!rename($uploaded_file_info['file'], $new_file_path)) {
         error_log('Failed to move file to protected folder');
         // Attempt to delete the initially uploaded file if moving fails
         @unlink($uploaded_file_info['file']);
         return new WP_REST_Response('Error moving file to protected folder', 500);
     }
 
     // Delete the initially uploaded file
     @unlink($uploaded_file_info['file']);
 
     // Build the URL to the uploaded file
    $new_file_url = $protected_folder_url . $sanitized_file_name;

    // Return the new file URL in the response
    return new WP_REST_Response(array('message' => 'File uploaded and moved successfully', 'url' => $new_file_url), 200);
}


function fungate_handle_media_delete( WP_REST_Request $request ) {
    $protected_folder_path = WP_PLUGIN_DIR . '/fungate/protected-folder/';

    // Use the custom sanitization function
    $file_name = fungate_sanitize_file_name($request->get_param('file'));
    $file_path = $protected_folder_path . $file_name;

    if (file_exists($file_path)) {
        unlink($file_path);
        return new WP_REST_Response('File deleted successfully', 200);
    } else {
        return new WP_REST_Response('File not found', 404);
    }
}

function fungate_sanitize_file_name($filename) {
    // Use wp_basename to get a safe file name base
    $filename = wp_basename($filename);
    // Remove any characters that are not word characters, white space, digits, or certain symbols
    $filename = preg_replace("([^\w\s\d\-_~,;:\[\]\(\).])", '', $filename);
    // Remove any instances of two or more periods
    $filename = preg_replace("([\.]{2,})", '', $filename);
    return $filename;
}
