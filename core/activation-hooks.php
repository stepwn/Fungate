<?php
// Function to create the protected-folder and .htaccess file
function fungate_create_protected_folder() {
    $protected_folder_path = plugin_dir_path(__FILE__) . 'protected-folder/';
    $htaccess_content = "# Deny all access from outside\n"
                    ."Order deny,allow\n"
                    ."Deny from all\n";

    if (!file_exists($protected_folder_path)) {
        // Create the protected folder
        mkdir($protected_folder_path, 0755, true);

        // Create and write the .htaccess file
        $htaccess_file_path = $protected_folder_path . '.htaccess';
        file_put_contents($htaccess_file_path, $htaccess_content);
    }
}

// Hook the function to plugin activation
register_activation_hook(__FILE__, 'fungate_create_protected_folder');
