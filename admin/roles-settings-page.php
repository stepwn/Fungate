<?php
defined('ABSPATH') or die('No script kiddies please!');

function fungate_enqueue_role_settings_scripts() {
    wp_enqueue_script('fungate-roles-settings', plugin_dir_url(__FILE__) . 'js/fungate-roles-settings.js', array('jquery'), null, true);

    // Create the nonce
    $nonce = wp_create_nonce('fungate_update_roles');

    $script_data = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => $nonce,
    );
    wp_localize_script('fungate-roles-settings', 'fungate_vars', $script_data);
}

add_action('admin_enqueue_scripts', 'fungate_enqueue_role_settings_scripts');

function fungate_roles_page(){
    fungate_admin_page_render();
    ?>
    <div class="fungate-wrap">
        <h1>Fungate Role Settings</h1>
        <form id="fungate_roles_form" method="post" action="">
        <?php
            // Retrieve the settings
            $settings = get_option('fungate_nft_roles_settings');

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
                    <td>Enable WordPress Role Modification based on NFT ownership</td>
                </tr>
                <tr>
                    <th scope="row"><label for="default_nft_role">Default NFT Role</label></th>
                    <td>
                    <select id="default_nft_role" name="default_nft_role">
                        <?php 
                        global $wp_roles;
                        $all_roles = array_reverse($wp_roles->roles);
                        $default_nft_role = get_option('default_nft_role', '');
                        foreach ($all_roles as $role_name => $role_info) {
        
                            ?>
                            <option value="<?php echo $esc_attr($role_name); ?>" <?php selected($esc_attr($role_name), strtolower($default_nft_role)); ?>>
                                <?php echo esc_html($role_info['name']); ?>
                            </option>
                        <?php } // End foreach ?>
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
                            foreach ($all_roles as $role_name => $role_info) {
                                ?>
                                <option value="<?php echo $esc_attr($role_name); ?>" <?php selected($esc_attr($role_name), $settings['selected_role']); ?>>
                                    <?php echo esc_html($role_info['name']); ?>
                                </option>
                            <?php } // End foreach ?>
                        </select>

                    </td>
                </tr>
                <?php
                    foreach ($all_roles as $role_name => $role_info) {
                    ?>
                        <tr class="role-row fungate-role-inputs" data-role="<?php echo $esc_attr($role_name); ?>" style="<?php echo ($esc_attr($role_name) == $settings['selected_role']) ? '' : 'display: none;'; ?>">
                            <td>
                                <div>
                                    <label for="nft_role_<?php echo $esc_attr($role_name); ?>_minter">Minter</label>
                                    <input type="text" id="nft_role_<?php echo $esc_attr($role_name); ?>_minter" name="nft_roles[<?php echo $esc_attr($role_name); ?>][minter]" value="<?php echo esc_attr($settings['nft_roles'][$role_name]['minter'] ?? ''); ?>" />
                                </div>
                                <div>
                                    <label for="nft_role_<?php echo $esc_attr($role_name); ?>_token">Token</label>
                                    <input type="text" id="nft_role_<?php echo $esc_attr($role_name); ?>_token" name="nft_roles[<?php echo $esc_attr($role_name); ?>][token]" value="<?php echo esc_attr($settings['nft_roles'][$role_name]['token'] ?? ''); ?>" />
                                </div>
                                <div>
                                    <label for="nft_role_<?php echo $esc_attr($role_name); ?>_nft_id">NFT ID</label>
                                    <input type="text" id="nft_role_<?php echo $esc_attr($role_name); ?>_nft_id" name="nft_roles[<?php echo $esc_attr($role_name); ?>][nft_id]" value="<?php echo esc_attr($settings['nft_roles'][$role_name]['nft_id'] ?? ''); ?>" />
                                </div>
                            </td>
                        </tr>
                <?php } // End foreach ?>

            </table>
            <p>
                <button type="button" id="fungate_save_btn" class="button-primary">Save Changes</button>
            </p>
        </form>
    </div>
    <?php
}

function fungate_save_fungate_roles_ajax() {
    check_ajax_referer('fungate_update_roles', 'nonce_field_name');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have permission to perform this action.');
    }

    // Parse serialized data
    parse_str($_POST['data'], $parsed_data);

    // Initialize an array for sanitized data
    $sanitized_data = array();

    // Sanitize each field
    $sanitized_data['nft_roles_enabled'] = isset($parsed_data['nft_roles_enabled']) ? 1 : 0;
    $sanitized_data['default_nft_role'] = isset($parsed_data['default_nft_role']) ? sanitize_text_field($parsed_data['default_nft_role']) : '';
    $sanitized_data['selected_role'] = isset($parsed_data['selected_role']) ? sanitize_text_field($parsed_data['selected_role']) : '';

    // Sanitize nft_roles if it's set
    if (isset($parsed_data['nft_roles']) && is_array($parsed_data['nft_roles'])) {
        $sanitized_data['nft_roles'] = array_map('fungate_sanitize_nft_roles', $parsed_data['nft_roles']);
    } else {
        $sanitized_data['nft_roles'] = array();
    }

    update_option('fungate_nft_roles_settings', $sanitized_data);
    wp_send_json_success('Settings saved.');
}
add_action('wp_ajax_save_fungate_roles', 'fungate_save_fungate_roles_ajax');

// Helper function to sanitize nft_roles
function fungate_sanitize_nft_roles($role_settings) {
    return array_map('sanitize_text_field', $role_settings);
}


