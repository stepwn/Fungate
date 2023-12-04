<?php
// Add AJAX endpoint to retrieve user's NFTs
add_action('wp_ajax_fungate_get_user_nfts', 'fungate_get_user_nfts');
add_action('wp_ajax_nopriv_fungate_get_user_nfts', 'fungate_get_user_nfts'); // For non-logged-in users

function fungates_get_user_nfts() {
    // Check if the user is logged in
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        
        // Retrieve the stored NFTs from user meta
        $nfts = get_user_meta($user_id, 'nfts', true);
        
        // Return the NFTs as JSON response
        if ($nfts) {
            wp_send_json_success($nfts);
        } else {
            wp_send_json_error('No NFTs found for the user.');
        }
    }  else {
        wp_send_json_error('User is not logged in.');
    }
}

// Add JavaScript code to the footer
function add_nfts_retrieval_script() {
    ?>
    <script>
        function fungate_get_nfts() {
            jQuery.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php'); ?>', 
                data: {
                    action: 'fungate_get_user_nfts'
                },
                success: function(response) {
                    console.log(response);
                    if (response.success) {
                        var nfts = response.data;
                        console.log(nfts); // Log the retrieved NFTs here
                    } else {
                        console.error(response.data);
                    }
                },
                error: function(errorThrown) {
                    console.error(errorThrown);
                }
            });
        }
    </script>
    <?php
}
add_action('wp_footer', 'add_nfts_retrieval_script');