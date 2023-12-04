<?php
function fungate_embed_protected_media($atts) {
    $atts = shortcode_atts(array(
        'src' => '',
        'text' => 'Download' // Default text is "Download"
    ), $atts);

    // Add access checks here to ensure only authorized users can access the media
    $file_url = $atts['src'];
    $file_name = basename($file_url);
    $button_text = esc_attr($atts['text']); // Sanitize and use the provided text

    $nonce = wp_create_nonce('fungate_download_' . $file_name);

    return "<a class='fungate-download-button' href='" . admin_url('admin-ajax.php?action=fungate_serve_protected_file&file=' . urlencode($file_url) . '&nonce=' . $nonce) . "' download><button class='wp-block-button__link wp-element-button'>$button_text</button></a>";
}
add_shortcode('fungate_media_download', 'fungate_embed_protected_media');



function fungate_serve_protected_file() {
    if (isset($_GET['file']) && isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'fungate_download_' . basename($_GET['file']))) {
        $file_url = sanitize_text_field($_GET['file']); // Get the full file URL
        $file_name = basename(parse_url($file_url, PHP_URL_PATH)); // Extract the file name

        $protected_folder_path = plugin_dir_path(dirname(__FILE__)) . 'protected-folder/';
        $file_path = $protected_folder_path . $file_name;
        
        if (file_exists($file_path)) {
            $mime_type = mime_content_type($file_path);

            header('Content-Type: ' . $mime_type);
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
            readfile($file_path);
            exit;
        } else {
            http_response_code(404);
            echo 'File not found, ' . $file_path;
            exit;
        }
    } else {
        http_response_code(400);
        echo 'Unauthorized access';
        exit;
    }
}
add_action('wp_ajax_fungate_serve_protected_file', 'fungate_serve_protected_file');
add_action('wp_ajax_nopriv_fungate_serve_protected_file', 'fungate_serve_protected_file');

function fungate_stream_protected_media() {
    $file_url = isset($_GET['file']) ? sanitize_text_field($_GET['file']) : '';

    if (!$file_url || !isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'fungate_stream_' . basename($file_url))) {
        http_response_code(400);
        echo 'Unauthorized access';
        exit;
    }

    // Extract the filename from the provided URL
    $file_name = basename(parse_url($file_url, PHP_URL_PATH));

    $file_path = plugin_dir_path(__FILE__) . 'protected-folder/' . $file_name;

    if (!file_exists($file_path)) {
        die('File not found.');
    }

    $chunkSize = 15 * 1024 * 1024; // Stream in 15MB chunks
    $handle = fopen($file_path, 'rb');

    $fileSize = filesize($file_path);
    $length = $fileSize; // Total length
    $start = 0; // Start byte
    $end = $fileSize - 1; // End byte

    header('Content-Type: ' . mime_content_type($file_path));
    header("Accept-Ranges: bytes");
    header("Content-Length: " . $length);

    while (!feof($handle) && ($p = ftell($handle)) <= $end) {
        if ($p + $chunkSize > $end) {
            $chunkSize = $end - $p + 1;
        }
        set_time_limit(0); // Reset time limit for big files
        echo fread($handle, $chunkSize);
        flush(); // Free up memory
    }

    fclose($handle);
    exit;
}
add_action('wp_ajax_fungate_stream_protected_media', 'fungate_stream_protected_media');
add_action('wp_ajax_nopriv_fungate_stream_protected_media', 'fungate_stream_protected_media');

function fungate_stream_media_shortcode($atts) {
    $a = shortcode_atts(array(
        'type' => 'image', // default type is video, can be changed to audio or image
        'src' => '' // the full URL of the media
    ), $atts);

    $nonce = wp_create_nonce('fungate_stream_' . basename($a['src']));

    $output = '';

    if ($a['type'] === 'video') {
        $output .= '<video controls width="100%">';
        $output .= '<source src="' . admin_url('admin-ajax.php') . '?action=fungate_stream_protected_media&file=' . urlencode($a['src']) . '&nonce=' . $nonce . '" type="video/mp4">';
        $output .= 'Your browser does not support the video tag.';
        $output .= '</video>';
    } elseif ($a['type'] === 'audio') {
        $output .= '<audio controls>';
        $output .= '<source src="' . admin_url('admin-ajax.php') . '?action=fungate_stream_protected_media&file=' . urlencode($a['src']) . '&nonce=' . $nonce . '" type="audio/mp3">';
        $output .= 'Your browser does not support the audio element.';
        $output .= '</audio>';
    } elseif ($a['type'] === 'image') {
        $output .= '<img width="100%"src="' . admin_url('admin-ajax.php') . '?action=fungate_stream_protected_media&file=' . urlencode($a['src']) . '&nonce=' . $nonce . '" alt="Protected Image">';
    }

    return $output;
}
add_shortcode('fungate_media', 'fungate_stream_media_shortcode');


function fungate_media_page() {
    if (!current_user_can('upload_files')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    // Handle file deletions
    if (isset($_GET['delete']) && !empty($_GET['file'])) {
        $protected_folder_path = plugin_dir_path(dirname(__FILE__)) . 'protected-folder/';
        $file_name = sanitize_file_name($_GET['file']);
        $file_path = $protected_folder_path . $file_name;
        
        if (file_exists($file_path)) {
            unlink($file_path); // Delete the file
            echo '<div class="notice notice-success"><p>File deleted successfully!</p></div>';
        }
    }
    
    // Handle media uploads
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_FILES)) {
        $protected_folder_path = plugin_dir_path(dirname(__FILE__)) . 'protected-folder/';
        
        // Create the protected folder if it doesn't exist
        if (!file_exists($protected_folder_path)) {
            mkdir($protected_folder_path, 0755, true);
        }
        
        $file = $_FILES['file'];
        $file_path = $protected_folder_path . $file['name'];
        
        // Move the uploaded file to the protected folder
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            echo '<div class="notice notice-success"><p>File uploaded successfully!</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Error uploading file.</p></div>';
        }
    }
    
    // Display media upload form
    echo '<div class="fungate-wrap">';
    echo '<h1>Fungate Media</h1>';
    echo '<p>Welcome to the Fungate Media dashboard. Here, you can easily upload and manage your media files. Follow the instructions below to get started:</p>';
    
    echo '<h2>Upload Media</h2>';
    echo '<p>Select a file from your device and click "Upload" to add it to your Fungate Media library.</p>';
    echo '<form method="POST" enctype="multipart/form-data">';
    echo '<input type="file" name="file">';
    echo '<input type="submit" value="Upload" class="button button-primary">';
    echo '</form>';
    
    echo '<h2>Uploaded Files</h2>';
    echo '<p>Below is a list of your uploaded files. You can copy the URL or shortcode for embedding, or delete files as needed.</p>';
    
    $protected_folder_path = plugin_dir_path(dirname(__FILE__)) . 'protected-folder/';
    $files = scandir($protected_folder_path);
    
    if (!empty($files)) {
        echo '<ul>';
        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && $file != '.htaccess') {
                $file_url = plugin_dir_url(dirname(__FILE__)) . 'protected-folder/' . urlencode($file);
                echo '<li>' . $file . ' - <button class="copy-url-button" data-url="' . $file_url . '">Copy URL</button> - <a href="?page=fungate-media&delete=true&file=' . urlencode($file) . '" class="delete-button" onclick="return confirm(\'Are you sure you want to delete this file?\')">Delete</a></li>';
            }
        }
        echo '</ul>';
    } else {
        echo '<p>No files uploaded.</p>';
    }
    
    echo '</div>'; // End of wrap div

    // JavaScript for copying URL and shortcode to clipboard
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const copyUrlButtons = document.querySelectorAll(".copy-url-button");
            
            copyUrlButtons.forEach(button => {
                button.addEventListener("click", function() {
                    const url = button.getAttribute("data-url");
                    copyToClipboard(url);
                    alert("URL copied to clipboard: " + url);
                });
            });
            
            function copyToClipboard(text) {
                const textarea = document.createElement("textarea");
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand("copy");
                document.body.removeChild(textarea);
            }
        });
    </script>';
}
