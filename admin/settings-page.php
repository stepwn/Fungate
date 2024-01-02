<?php
defined('ABSPATH') or die('No script kiddies please!');
function fungate_settings_page() {
    $plugin_root_path = plugin_dir_path( __DIR__ );
    // Check if Chainhopper is installed
    $chainhopper_installed = file_exists($plugin_root_path. 'core/chainhopper');
    // Check if the 'Activate Chainhopper' button was pressed
    if (isset($_POST['activate_chainhopper'])) {
        if (get_option('fungate_license') !== 'FREE FOREVER' && get_option('fungate_license') !== '') {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            WP_Filesystem();
            global $wp_filesystem;
    
            error_log('attempting to check for chainhopper');
            $license_key = sanitize_text_field(get_option('fungate_license'));
        
            if (current_user_can('manage_options')) {
                $response = wp_remote_post('https://fungate.io/wp-content/plugins/fungate/core/license/check.php', array(
                    'body' => array('license_key' => $license_key),
                    'sslverify' => true,
                ));
                
                if (is_wp_error($response)) {
                    error_log('HTTP Error: ' . $response->get_error_message());
                } else {
                    $data = json_decode(wp_remote_retrieve_body($response));
                }
                
                if ($response === false) {
                    error_log('cURL Error: ' . curl_error($curl));
                } else {
                    $data = json_decode($response);
        
                    if ($data && $data->status === 'chainhopper' && !empty($data->nonce)) {
                        $target_folder = $plugin_root_path. 'core/chainhopper';
    
                        error_log($target_folder);
                        // Check if Chainhopper is already installed
                        if (!file_exists($target_folder)) {
                            // Attempt to create the target directory if it doesn't exist
                            if (!mkdir($target_folder, 0755, true) && !is_dir($target_folder)) {
                                error_log('Error: Unable to create Chainhopper directory.');
                            } else {
                                error_log('downloading chainhopper');
                                $download_link = 'https://fungate.io/wp-content/plugins/fungate/core/license/download_chainhopper.php?nonce=' . $data->nonce;
        
                                $downloaded_data = wp_remote_get($download_link, array(
                                    'sslverify' => true,
                                    'redirection' => true, // Automatically follow redirects
                                ));
                                
                                if (is_wp_error($downloaded_data)) {
                                    error_log('HTTP Error: ' . $downloaded_data->get_error_message());
                                } else {
                                    $downloaded_data = wp_remote_retrieve_body($downloaded_data);
                                }
                                
    
                                // Check for errors in the cURL session
                                if ($downloaded_data === false) {
                                    error_log('cURL Error: ' . curl_error($curl));
                                } else {
                                    // Specify the path where you want to save the downloaded file
                                    $temp_file = tempnam(sys_get_temp_dir(), 'chainhopper_');
    
                                    // Save the downloaded data to the file
                                    file_put_contents($temp_file, $downloaded_data);
    
                                    // Close the cURL session
                                    curl_close($curl);
                                    error_log('successfully downloaded. preparing to unzip...');
                                    // Check for file writing errors
                                    if ($downloaded_data === false) {
                                        error_log('Error writing downloaded file.');
                                    } else {
                                        $result = unzip_file($temp_file, $target_folder);
                                        if (is_wp_error($result)) {
                                            error_log('Fungate Plugin Error: ' . $result->get_error_message());
                                        } else {
                                            // Successfully downloaded and unzipped
    
                                            if (is_wp_error($result)) {
                                                error_log('Fungate Plugin Error: ' . $result->get_error_message());
                                            } else {
                                                $plugin_file = plugin_dir_path(__FILE__) . '../fungate.php';
                                                $include_line = "include_once('core/chainhopper/chainhopper-admin.php');";
    
                                                // Read the current contents of the plugin file
                                                $plugin_contents = file_get_contents($plugin_file);
    
                                                // Check if the line already exists
                                                if (strpos($plugin_contents, $include_line) === false) {
                                                    // Append the include line to the plugin file contents
                                                    $plugin_contents .= "\n" . $include_line;
    
                                                    // Write the modified contents back to the plugin file
                                                    file_put_contents($plugin_file, $plugin_contents);
                                                    // After successful installation, redirect to the Chainhopper settings page
                                                    //wp_redirect(admin_url('admin.php?page=fungate-chainhopper-settings'));
                                                    @unlink($temp_file);
                                                    exit;
                                                }
    
                                            }
    
                                            // Clean up by deleting the temporary file
                                            @unlink($temp_file);
                                        }
                                        
                                    }
                                }
    
                            }
                        } else {
                            error_log('Chainhopper is already installed');
                        }
                    } else {
                        error_log("License key is invalid or not 'chainhopper'");
                    }
                }
                curl_close($curl);
            }
        }
    }
    fungate_admin_page_render();
    
    

    // Check if WalletConnect project ID and Loopring API key are not set
    $walletconnect_project_id = get_option('wc_projectId');
    $loopring_api_key = get_option('loopring_api_key');
    
    // Display a notification if either value is not set
    if (empty($walletconnect_project_id)) {
        echo '<div class="notice notice-error"><p><strong>Fungate Plugin:</strong> Please set your <b>WalletConnect project ID</b> in the settings to enable full functionality.</p></div>';
    }
    if (empty($loopring_api_key) && get_option('fungate_license') == 'FREE FOREVER') {
        echo '<div class="notice notice-error"><p><strong>Fungate Plugin:</strong> Please set your <b>Loopring Api Key</b> or <b>Fungate License</b> in the settings to enable full functionality.</p></div>';
    }
    // Get the current permalink structure
    $permalink_structure = get_option('permalink_structure');

    // Check if permalinks are set to plain (empty means plain)
    if (empty($permalink_structure)) {
        // Display notice in the WordPress admin
        echo '<div class="notice notice-error"><p><strong>Error:</strong> Your permalink structure is set to "plain." This may cause issues with the REST API. Please go to <a href="' . admin_url('options-permalink.php') . '">Settings > Permalinks</a> and change it to a different setting.</p></div>';
    }


    // Check for the GMP PHP extension
if ( !extension_loaded('gmp') && get_option('fungate_license') == 'FREE FOREVER' ) {
    // Get the current PHP version and split it at the dots
    $php_version_parts = explode('.', phpversion());

    // Concatenate the first two parts to get the version in the format 'x.x'
    $short_php_version = $php_version_parts[0] . '.' . $php_version_parts[1];

    // Display the error message with the modified PHP version
    echo '<div class="notice notice-error"><p>php' . $short_php_version . '-gmp extension is not installed. Please run <i>sudo apt install php' . $short_php_version . '-gmp</i> on your server, or <a href="https://fungate.io/shop">get a license</a></p></div>';
}
    ?>
    <div class="fungate-wrap">
        <h1>Fungate Plugin Settings</h1>
        <form method="post" action="">
    <?php if (!$chainhopper_installed && get_option('fungate_license') !== 'FREE FOREVER' && get_option('fungate_license') !== ''): ?>
        <p>
            <button type="submit" name="activate_chainhopper" onclick="changeButtonText(this);" class="button button-primary">Activate Chainhopper</button>
        </p>
    <?php endif; ?>
        </form>

        <script>
        function changeButtonText(button) {
            button.textContent = 'Installing Chainhopper. This may take a minute or two. Please wait...';
            setTimeout(function() {
                button.disabled = true;
            }, 100); // Delay disabling the button
        }
        </script>

        <form method="post" action="options.php">
            <?php settings_fields('fungate_settings'); ?>
            <?php do_settings_sections('fungate_settings'); ?>

            <h2>General Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="fungate_license">Fungate License</label></th>
                    <td><input type="text" id="fungate_license" name="fungate_license" value="<?php echo esc_attr(get_option('fungate_license')); ?>" /></td>
                    <td>Pro and Chainhopper Licenses available. <a href="https://fungate.io">Learn More</a></td>
                </tr>
                <tr>
                    <th scope="row"><label for="loopring_api_key">Loopring API Key</label></th>
                    <td><input type="password" id="loopring_api_key" name="loopring_api_key" value="<?php echo esc_attr(get_option('loopring_api_key')); ?>" /></td>
                    <td>Obtained by exporting your Loopring account at <a href="https://loopring.io">https://loopring.io</a></td>
                </tr>
                <tr>
                    <th scope="row"><label for="wc_projectId">WalletConnect Project ID</label></th>
                    <td><input type="text" id="wc_projectId" name="wc_projectId" value="<?php echo esc_attr(get_option('wc_projectId')); ?>" /></td>
                    <td>Obtained for free at <a href="https://cloud.walletconnect.com/sign-in">https://cloud.walletconnect.com/sign-in</a></td>
                </tr>
                <tr>
                <th scope="row">
                    <label for="fungate_default_fail_message">Default Fail Message HTML</label>
                    </th>
                    <td>
                        <textarea id="fungate_default_fail_message" name="fungate_default_fail_message" rows="4" cols="50"><?php echo esc_textarea(get_option('fungate_default_fail_message', "<span class='dashicons dashicons-lock' style='font-size: 2.5em; width: 2.5em; display: block; margin: auto; box-sizing: border-box;'></span><br><b>You do not own the required NFT to view this content.</b><br><small>If you recently acquired the NFT, it may take up to 30 minutes for the transaction to post and be available.</small>")); ?></textarea>
                    </td>
                    <td>
                        Specify the default message HTML to be displayed when access is denied due to NFT ownership verification failure.
                    </td>
                </tr>
                <tr>
                <th scope="row">
                    <label for="fungate_unlock_button_css">Default Unlock Content Button Inline CSS</label>
                    </th>
                    <td>
                        <textarea id="fungate_unlock_button_css" name="fungate_unlock_button_css" rows="4" cols="50"><?php echo esc_textarea(get_option('fungate_unlock_button_css', "")); ?></textarea>
                    </td>
                    <td>
                        Add custom inline CSS to the default unlock content button.
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="fungate_add_accountButton_to_fail">Add Account button to fail messages</label></th>
                    <td><input type="checkbox" id="fungate_add_accountButton_to_fail" name="fungate_add_accountButton_to_fail" value="1" <?php checked(1, get_option('fungate_add_accountButton_to_fail', 1), true); ?> /></td>
                    <td>Add the Account/wallet button to failed gate messages by default</td>
                </tr>
                <tr>
                    <th scope="row"><label for="fungate_nft_enabled">Enable Fungate NFT Posts</label></th>
                    <td><input type="checkbox" id="fungate_nft_enabled" name="fungate_nft_enabled" value="1" <?php checked(1, get_option('fungate_nft_enabled', 1), true); ?> /></td>
                    <td>Enable or disable Fungate NFT custom post type.</td>
                </tr>
                <tr>
                    <th scope="row"><label for="fungate_tinymce_enabled">Enable TinyMCE editor button</label></th>
                    <td><input type="checkbox" id="fungate_tinymce_enabled" name="fungate_tinymce_enabled" value="1" <?php checked(1, get_option('fungate_tinymce_enabled', 1), true); ?> /></td>
                    <td>Enable or disable token gate button in the TinyMCE editor.</td>
                </tr>
                <tr>
                    <th scope="row"><label for="fungate_buddypress_enabled">Enable Fungate NFT Groups in BuddyPress</label></th>
                    <td><input type="checkbox" id="fungate_buddypress_enabled" name="fungate_buddypress_enabled" value="1" <?php checked(1, get_option('fungate_buddypress_enabled', 1), true); ?> /></td>
                    <td>Enable or disable Fungate integration with BuddyPress.</td>
                </tr>
                <tr>
                    <th scope="row"><label for="fungate_woocommerce_enabled">Enable Fungate integration with WooCommerce</label></th>
                    <td><input type="checkbox" id="fungate_woocommerce_enabled" name="fungate_woocommerce_enabled" value="1" <?php checked(1, get_option('fungate_woocommerce_enabled', 1), true); ?> /></td>
                    <td>Enable or disable Fungate integration with WooCommerce.</td>
                </tr>
            </table>
            <?php submit_button(); ?>

        </form>
    </div>
    <?php
}