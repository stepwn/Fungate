<?php
// admin css bug fixes
function enqueue_custom_admin_style() {
    wp_enqueue_style('admin-custom', plugin_dir_url(__FILE__) . '/css/admin-custom.css', false, '1.0.0');
}
add_action('admin_enqueue_scripts', 'enqueue_custom_admin_style');

add_action('admin_menu', 'fungate_plugin_menu');
function fungate_plugin_menu() {
    $icon_url = plugins_url('/media/fungate_logo.png', __FILE__);

    // Add the Fungate top-level menu
    add_menu_page('Fungate Plugin', 'Fungate', 'manage_options', 'fungate', 'fungate_menu_page', $icon_url);

    // Add the Posts submenu under Fungate
    add_submenu_page('fungate', 'Fungate NFTs', 'Posts', 'manage_options', 'edit.php?post_type=fungate_nft');

     // Add the Fungate Media submenu under Fungate
     add_submenu_page('fungate', 'Fungate Media', 'Fungate Media', 'manage_options', 'fungate-media', 'fungate_media_page');

     // Add the Settings submenu under Fungate
    add_submenu_page('fungate', 'Roles', 'Roles', 'manage_options', 'fungate-roles-settings', 'fungate_roles_page');

    // Add the Settings submenu under Fungate
    add_submenu_page('fungate', 'Fungate Settings', 'Settings', 'manage_options', 'fungate-settings', 'fungate_settings_page');

    // Add the Settings submenu under Fungate
    add_submenu_page('fungate', 'Shortcode Generator', 'Shortcode Generator', 'manage_options', 'fungate-shortcode-generator', 'fungate_shortcode_generator_page');

    // Customize the icon size for the top-level menu
    echo '<style>.toplevel_page_fungate .wp-menu-image img { width: 24px; }</style>';

    // Remove the duplicate sub menu item
    remove_submenu_page('fungate', 'Fungate');
}
function fungate_shortcode_generator_page() {
    include(plugin_dir_path(__FILE__) . 'html/editor.html');
}

function fungate_roles_page(){
    ?>
    <style>
        .fungate-role-inputs {
            display: none;
        }
    </style>
    <h1>Fungate Role Settings</h1>
    <form method="post" action="">
        <?php
        // Use a unique option name for this page's settings
        $option_name = 'fungate_nft_roles_settings';
        
        if (isset($_POST['fungate_submit'])) {
            // Handle form submission and update settings
            update_option($option_name, $_POST);
            ?>
            <div class="updated"><p><strong>Settings saved.</strong></p></div>
            <?php
        }

        // Retrieve the settings
        $settings = get_option($option_name);

        // Initialize default values if settings are empty
        $settings = !empty($settings) ? $settings : array(
            'nft_roles_enabled' => 0,
            'selected_role' => '',
            'nft_roles' => array(),
        );
        
        // Display form fields
        ?>
        <table>
            <tr>
                <th scope="row"><label for="nft_roles_enabled">Enable NFT Roles</label></th>
                <td><input type="checkbox" id="nft_roles_enabled" name="nft_roles_enabled" value="1" <?php checked(1, $settings['nft_roles_enabled'], true); ?> /></td>
                <td>Enable Wordpress Role Modification based on NFT ownership</td>
            </tr>
            <tr>
                <th scope="row"><label for="default_nft_role">Default NFT Role</label></th>
                <td>
                    <select id="default_nft_role" name="default_nft_role">
                    <?php 
                    global $wp_roles;
                    $all_roles = array_reverse($wp_roles->roles);
                    $default_nft_role = get_option('default_nft_role', '');
                    foreach ($all_roles as $role_name => $role_info): 
                    ?>
                        <option value="<?php echo strtolower($role_name); ?>" <?php selected(strtolower($role_name), strtolower($default_nft_role));?>><?php echo $role_info['name']; ?></option>
                    <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
        <hr>
        <h3>Select a role to change gating logic</h3>
        <table class="form-table">
            <tr>
                <td>
                    <label for="selected_role">Select Role:</label>
                    <select id="selected_role" name="selected_role">
                    <option value="" default>Select Role</option>
                        <?php
                        global $wp_roles;
                        $all_roles = $wp_roles->roles;
                        foreach ($all_roles as $role_name => $role_info):
                        ?>
                            <option value="<?php echo $role_name; ?>" <?php selected($role_name, $settings['selected_role']); ?>><?php echo $role_info['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <?php
            foreach ($all_roles as $role_name => $role_info):
            ?>
            <tr class="role-row fungate-role-inputs" data-role="<?php echo $role_name; ?>" style="<?php echo ($role_name == $settings['selected_role']) ? '' : 'display: none;'; ?>">
                <td>
                    <div>
                        <label for="nft_role_<?php echo $role_name; ?>_minter">Minter</label>
                        <input type="text" id="nft_role_<?php echo $role_name; ?>_minter" name="nft_roles[<?php echo $role_name; ?>][minter]" value="<?php echo esc_attr($settings['nft_roles'][$role_name]['minter'] ?? ''); ?>" />
                    </div>
                    <div>
                        <label for="nft_role_<?php echo $role_name; ?>_token">Token</label>
                        <input type="text" id="nft_role_<?php echo $role_name; ?>_token" name="nft_roles[<?php echo $role_name; ?>][token]" value="<?php echo esc_attr($settings['nft_roles'][$role_name]['token'] ?? ''); ?>" />
                    </div>
                    <div>
                        <label for="nft_role_<?php echo $role_name; ?>_nft_id">NFT ID</label>
                        <input type="text" id="nft_role_<?php echo $role_name; ?>_nft_id" name="nft_roles[<?php echo $role_name; ?>][nft_id]" value="<?php echo esc_attr($settings['nft_roles'][$role_name]['nft_id'] ?? ''); ?>" />
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p>
            <input type="submit" name="fungate_submit" class="button-primary" value="Save Changes" />
        </p>
        <script>
        jQuery(document).ready(function($) {
            $('#selected_role').on('change', function() {
                var selectedRole = $(this).val();
                $('.fungate-role-inputs').hide();
                $('.fungate-role-inputs[data-role="' + selectedRole + '"]').show();
            });
        });
        </script>
    </form>
    <?php
}





function fungate_menu_page(){
    ?>
    <div class="fungate-wrap">
        <h1>Welcome to Fungate Plugin!</h1>
        <p>Thank you for using Fungate! This plugin helps you manage and display NFT gated content on your website.</p>
        <p>For more information and updates, please visit:</p>
        <ul>
            <li><a href="https://github.com/stepwn/Fungate" target="_blank">GitHub Repository</a></li>
            <li><a href="https://discord.gg/GYTEp72SdD" target="_blank">Discord Community</a></li>
            <li><a href="https://fungate.dev" target="_blank">Looppress.dev Website</a></li>
        </ul>
        
        <h2>Plugin Pages</h2>
        <p>You can manage your NFTs and configure settings using the following buttons:</p>
        <div class="fungate-plugin-buttons">
            <a class="button-primary" href="<?php echo admin_url('edit.php?post_type=fungate_nft'); ?>">Manage Posts</a>
            <a class="button-primary" href="<?php echo admin_url('admin.php?page=fungate-media'); ?>">Manage Protected Media</a>
            <a class="button-primary" href="<?php echo admin_url('admin.php?page=fungate-settings'); ?>">Configure Settings</a>
        </div>
        <br>
        <hr>
        <h1>Shortcode Usage</h1>
        <div class="fungate-shortcode-info">
            <h2>Fungate Shortcode: [fungate][/fungate]</h2>
            <p>The <code>[fungate][/fungate]</code> shortcode allows you to show content only to specific NFT owners.</p>
            <p>If any of the provided attributes match the attributes of a user's owned Loopring NFTs, the content inside the shortcode will be displayed. Otherwise, the content will not be served.</p>
            
            <div class="fungate-shortcode-example">
                <h3>Usage:</h3>
                <pre><code>[fungate nft="0xabc123" minter="0xabc123" contract="0xabc123"]Your content here[/fungate]</code></pre>
                * Only one of the nft, minter, or contract attributes needs to be set to work. If multiple are set it will only allow access if all the conditions are true.
                <pre><code>[fungate nft="0xabc123"]Your content here[/fungate]</code></pre>
            </div>
        </div>
        <div class="fungate-shortcode-info">
        <h2>Fungate Media Shortcode: [fungate_media]</h2>
            <p>The <code>[fungate_media]</code> shortcode allows you to selectively embed/stream protected media (.mp4, .mp3, .jpg, etc).</p>
            <p>First, upload your protected media file <a class="button-primary" href="<?php echo admin_url('admin.php?page=fungate-media'); ?>">Manage Protected Media</a></p>
        
            <div class="fungate-shortcode-example">
                <h3>Usage:</h3>
                <pre><code>[fungate_media type="video" src="https://yoursite.com/wp-content/plugins/Fungate/protected-content/your-file.mp4"]</code></pre>
                * Copy the full link to the protected file into the <code>src=""</code> attribute.<br>
                * Use the <code>type=""</code> attribute to your media type: image, video, or audio.
                <p>Fungate will stream the embedded media</p>
            </div>
        </div>
        <div class="fungate-shortcode-info">
        <h2>Fungate Media Download Shortcode: [fungate_media_download]</h2>
            <p>The <code>[fungate_media_download]</code> shortcode allows you to selectively serve protected media (.mp4, .zip, .webm, .obj, etc).</p>
            <p>First, upload your protected media file <a class="button-primary" href="<?php echo admin_url('admin.php?page=fungate-media'); ?>">Manage Protected Media</a></p>
        
            <div class="fungate-shortcode-example">
                <h3>Usage:</h3>
                <pre><code>[fungate_media_download src="https://yoursite.com/wp-content/plugins/Fungate/protected-content/your-file.zip"]</code></pre>
                * Copy the full link to the protected file into the <code>src=""</code> attribute.<br>
                * Use the <code>text="Download Button Text"</code> attribute to change the download button text.
                <p>Fungate will display a download button to serve the protected media file</p>
            </div>
        </div>
    </div>
    
    <style>
        .fungate-plugin-buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .fungate-shortcode-info {
            margin-top: 30px;
            border: 1px solid #ccc;
            padding: 15px;
            background-color: #f9f9f9;
        }
        .fungate-shortcode-example {
            margin-top: 15px;
            border-top: 1px dashed #ccc;
            padding-top: 15px;
        }
    </style>
    <?php
}

// Looppress Settings
add_action('admin_init', 'fungate_settings_init');
function fungate_settings_init() {
    register_setting('fungate_settings', 'loopring_api_key');
	register_setting('fungate_settings', 'wc_projectId');
    register_setting('fungate_settings', 'nft_roles');
    register_setting('fungate_settings', 'fungate_default_fail_message', ['default' => "<span class='dashicons dashicons-lock' style='font-size: 2.5em; width: 2.5em; display: block; margin: auto; box-sizing: border-box;'></span><br><b>You do not own the required NFT to view this content.</b><br><small>If you recently acquired the NFT, it may take up to 30 minutes for the transaction to post and be available.</small>"]);
    register_setting('fungate_settings', 'fungate_add_accountButton_to_fail', ['default' => 0]);
    register_setting('fungate_settings', 'nft_roles_enabled', ['default' => 0]);
    register_setting('fungate_settings', 'fungate_nft_enabled', ['default' => 1]);
    register_setting('fungate_settings', 'fungate_buddypress_enabled', ['default' => 0]);
    register_setting('fungate_settings', 'fungate_woocommerce_enabled', ['default' => 0]);
    register_setting('fungate_settings', 'fungate_tinymce_enabled', ['default' => 0]);
    register_setting('fungate_settings', 'fungate_license', ['default' => "FREE FOREVER"]);
    register_setting('fungate_settings', 'default_nft_role', ['default' => get_option('default_role')]);
}

function fungate_settings_page() {
    // Check if WalletConnect project ID and Loopring API key are not set
    $walletconnect_project_id = get_option('wc_projectId');
    $loopring_api_key = get_option('loopring_api_key');
    
    // Display a notification if either value is not set
    if (empty($walletconnect_project_id) || empty($loopring_api_key)) {
        echo '<div class="notice notice-error"><p><strong>Fungate Plugin:</strong> Please set your <b>WalletConnect project ID</b> and <b>Loopring API key</b> in the settings to enable full functionality.</p></div>';
    }

    // Check for the GMP PHP extension
if ( !extension_loaded('gmp') ) {
    // Get the current PHP version and split it at the dots
    $php_version_parts = explode('.', phpversion());

    // Concatenate the first two parts to get the version in the format 'x.x'
    $short_php_version = $php_version_parts[0] . '.' . $php_version_parts[1];

    // Display the error message with the modified PHP version
    echo '<div class="notice notice-error"><p>php' . $short_php_version . '-gmp extension is not installed. Please run <i>sudo apt install php' . $short_php_version . '-gmp</i> on your server, or <a href="https://fungate.dev/pro">get a pro license</a></p></div>';
}
    ?>
    <style>
    .fungate-wrap h2 {
        color: #333;
        padding-bottom: 10px;
        margin: 20px 0;
        border-bottom: 1px solid #cccccc;
    }
    .fungate-wrap .form-table th {
        font-weight: 600;
    }
    .fungate-wrap input[type="text"], .fungate-wrap input[type="password"] {
        width: 60%;
    }
    .fungate-wrap .nft-role-section div {
        margin-bottom: 10px;
    }
    </style>
    <div class="fungate-wrap">
        <h1>Fungate Plugin Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('fungate_settings'); ?>
            <?php do_settings_sections('fungate_settings'); ?>

            <h2>General Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="fungate_license">Looppress License</label></th>
                    <td><input type="text" id="fungate_license" name="fungate_license" value="<?php echo esc_attr(get_option('fungate_license')); ?>" /></td>
                    <td>If you can't install php-gmp on your server, we can handle signature verification. Pro licenses come with personalized support. <a href="#">Learn More</a></td>
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