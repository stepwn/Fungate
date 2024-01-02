<?php
defined('ABSPATH') or die('No script kiddies please!');
function fungate_roles_page(){
    fungate_admin_page_render();
    ?>
    <div class="fungate-wrap">
        <h1>Fungate Role Settings</h1>
        <form id="fungate_roles_form" method="post" action="">
        <?php
            // Use a unique option name for this page's settings
            $option_name = 'fungate_nft_roles_settings';
            
            if (isset($_POST['fungate_submit'])) {
                // Sanitize and validate the input data
                $validated_data = sanitize_and_validate_fungate_input($_POST);

                // Validate nonce
                $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';
                if (!wp_verify_nonce(sanitize_text_field( wp_unslash ($nonce)), 'fungate_submit_nonce')) {
                    wp_die('Security check failed.');
                }

                // Handle form submission and update settings
                update_option($option_name, $validated_data);
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
                            $safe_role_name = esc_attr($role_name); 
                            $safe_role_name_lower = strtolower($safe_role_name);
                            ?>
                            <option value="<?php echo $safe_role_name_lower; ?>" <?php selected($safe_role_name_lower, strtolower($default_nft_role)); ?>>
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
                                $safe_role_name = esc_attr($role_name);
                                ?>
                                <option value="<?php echo $safe_role_name; ?>" <?php selected($safe_role_name, $settings['selected_role']); ?>>
                                    <?php echo esc_html($role_info['name']); ?>
                                </option>
                            <?php } // End foreach ?>
                        </select>

                    </td>
                </tr>
                <?php
                    foreach ($all_roles as $role_name => $role_info) {
                        $safe_role_name = esc_attr($role_name);
                    ?>
                        <tr class="role-row fungate-role-inputs" data-role="<?php echo $safe_role_name; ?>" style="<?php echo ($safe_role_name == $settings['selected_role']) ? '' : 'display: none;'; ?>">
                            <td>
                                <div>
                                    <label for="nft_role_<?php echo $safe_role_name; ?>_minter">Minter</label>
                                    <input type="text" id="nft_role_<?php echo $safe_role_name; ?>_minter" name="nft_roles[<?php echo $safe_role_name; ?>][minter]" value="<?php echo esc_attr($settings['nft_roles'][$role_name]['minter'] ?? ''); ?>" />
                                </div>
                                <div>
                                    <label for="nft_role_<?php echo $safe_role_name; ?>_token">Token</label>
                                    <input type="text" id="nft_role_<?php echo $safe_role_name; ?>_token" name="nft_roles[<?php echo $safe_role_name; ?>][token]" value="<?php echo esc_attr($settings['nft_roles'][$role_name]['token'] ?? ''); ?>" />
                                </div>
                                <div>
                                    <label for="nft_role_<?php echo $safe_role_name; ?>_nft_id">NFT ID</label>
                                    <input type="text" id="nft_role_<?php echo $safe_role_name; ?>_nft_id" name="nft_roles[<?php echo $safe_role_name; ?>][nft_id]" value="<?php echo esc_attr($settings['nft_roles'][$role_name]['nft_id'] ?? ''); ?>" />
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
    <script>
    jQuery(document).ready(function($) {
        $('#fungate_save_btn').on('click', function() {
            var data = {
                'action': 'save_fungate_roles',
                'data': $('#fungate_roles_form').serialize()
            };
            $.post(ajaxurl, data, function(response) {
                alert('Settings saved');
            });
        });
        $('#selected_role').on('change', function() {
            var selectedRole = $(this).val();
            $('.fungate-role-inputs').hide();
            $('.fungate-role-inputs[data-role="' + selectedRole + '"]').show();
        });

    });
    </script>
    <?php
}

// AJAX handler function
add_action('wp_ajax_save_fungate_roles', 'fungate_save_fungate_roles_ajax');
function fungate_save_fungate_roles_ajax() {
    // Check if the user has permission to save settings
    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have permission to perform this action.');
    }

    $sanitized_data = sanitize_and_validate_fungate_input($_POST);

    // Save the settings
    update_option('fungate_nft_roles_settings', $sanitized_data);

    // Send a success message
    wp_send_json_success('Settings saved.');
}

/**
 * Sanitize and validate fungate input data.
 *
 * @param array $input_data The input data to sanitize and validate.
 * @return array Sanitized and validated data.
 */
function sanitize_and_validate_fungate_input($input_data) {
    $sanitized_data = wp_unslash($input_data);
    $validated_data = filter_input_array(INPUT_POST, get_validation_filters());

    return $validated_data;
}

/**
 * Get validation filters for fungate input data.
 *
 * @return array Validation filters.
 */
function get_validation_filters() {
    // Define your validation filters here
    $validation_filters = array(
        'nft_roles_enabled' => FILTER_VALIDATE_INT,
        'selected_role' => FILTER_SANITIZE_STRING,
        // Add more filters as needed for other fields
    );

    return $validation_filters;
}
