<?php
defined('ABSPATH') or die('No script kiddies please!');
/**
 * Shortcode for displaying the account button.
 *
 * @return string HTML for w3m-button.
 */
function fungate_account_shortcode() {
    return '<div class="fungate-w3m-button"><w3m-button balance="hide"></w3m-button></div>';
}
add_shortcode('fungate_account', 'fungate_account_shortcode');

/**
 * Shortcode for displaying the Fungate dashboard.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output for the dashboard.
 */
function fungate_dashboard_shortcode($atts) {
    $css = esc_attr(get_option("fungate_unlock_button_css"));
    $html = isset($atts['html']) ? $atts['html'] : "<button class='wp-block-button__link wp-element-button fungateButton' style='$css' onclick='fungate_unlock(this)'>Connect Wallet</button>";
    $redir = isset($atts['redir']) ? esc_attr($atts['redir']) : false;
    return $html;
}
add_shortcode('fungate_dashboard', 'fungate_dashboard_shortcode');

/**
 * Registers additional shortcodes for Fungate.
 */
function fungate_register_shortcode() {
    add_shortcode('fungate_button', 'fungate_dashboard_shortcode');
}
add_action('init', 'fungate_register_shortcode');

/**
 * Shortcode for Fungate membership content.
 *
 * @param array  $atts Shortcode attributes.
 * @param string $content Shortcode content.
 * @return string Processed shortcode content.
 */
function fungate_shortcode($content = null, $fail_message = '' ) {
    if (is_user_logged_in()) {
        // Retrieve user metadata and check for Ethereum address
        $user_id = get_current_user_id();
        $ethereum_address = get_user_meta($user_id, 'ethereum_address', true);

        if (!$ethereum_address) {
            return do_shortcode('[fungate_button]');
        }

        // Process the content with membership logic
        return $fail_message;
    } else {
        return do_shortcode('[fungate_button]');
    }
}
/**
 * Determines if a user has the required NFT membership based on provided attributes.
 *
 * @param string $selected_account Ethereum account of the user.
 * @param array  $attributes Attributes for the membership check.
 * @return bool True if the user has membership, false otherwise.
 */
function fungate_membership_shortcode( $atts, $content = null ) {
    $redir = false;
    $hasgate = true;
    if ( $content != "" ) {
        $redir = true;
    }

  // Check if no attributes are passed, then set default post metadata
    if (empty($atts)) {
        $post_id = get_the_ID(); // Get the current post ID
        $hasgate = false;

        $nft_id_from_metadata = get_post_meta($post_id, 'fungate_nft_id', true);
        $nft_minter_from_metadata = get_post_meta($post_id, 'fungate_minter', true);
        $nft_token_from_metadata = get_post_meta($post_id, 'fungate_token', true);
        $nft_contract_from_metadata = get_post_meta($post_id, 'fungate_contract', true);

        // Initialize $atts as an array
        $atts = [];

        if ($nft_id_from_metadata && $nft_id_from_metadata !== "") {
            $atts['nft_id'] = $nft_id_from_metadata;
            $hasgate = true;
        }
        if ($nft_minter_from_metadata && $nft_minter_from_metadata !== "") {
            $atts['minter'] = $nft_minter_from_metadata;
            $hasgate = true;
        }
        if ($nft_token_from_metadata && $nft_token_from_metadata !== "") {
            $atts['token'] = $nft_token_from_metadata;
            $hasgate = true;
        }
        if ($nft_contract_from_metadata && $nft_contract_from_metadata !== "") {
            $atts['contract'] = $nft_contract_from_metadata;
            $hasgate = true;
        }
    }


    if (!get_user_meta(get_current_user_id(), 'ethereum_address')) {
        // Only display contents if user is logged in
        return do_shortcode('[fungate_button redir="'.$redir.'" text="Sign in Web3"]');
    }

    // Extract attributes
    $attributes = shortcode_atts( [ 'contract' => '', 'token' => '', 'minter' => '', 'nft_id' => '', 'schedule' => '' ], $atts );

    // Check for schedule attribute and parse it if present
    if ( isset( $attributes['schedule'] ) && !empty( $attributes['schedule'] ) ) {
        $schedule_logic = json_decode( str_replace( "'", '"', $attributes['schedule'] ), true );
        if ( json_last_error() == JSON_ERROR_NONE && is_array( $schedule_logic ) ) {
            // Get the current date
            $current_date = new DateTime();
            //error_log("Current Datetime ".$current_date);
            $applicable_logic = null;

            // Iterate through the schedule logic and find the most recent applicable logic
            foreach ( $schedule_logic as $date => $logic ) {
                $schedule_date = DateTime::createFromFormat('Y-m-d H:i', $date);
                if ( $schedule_date <= $current_date ) {
                    $applicable_logic = $logic;
                } else {
                    break; // Since the logic is ordered, we can break early
                }
            }

            // If applicable logic is found, override the respective attributes
            if ( $applicable_logic !== null ) {
                foreach (['contract', 'token', 'minter', 'nft_id'] as $attr) {
                    if (isset($applicable_logic[$attr])) {
                        $attributes[$attr] = $applicable_logic[$attr];
                    }
                }
            }
        }
    }

    $fail_message = isset( $atts['fail-message'] ) ? $atts['fail-message'] : get_option("fungate_default_fail_message","<span class='dashicons dashicons-lock' style='font-size: 2.5em; width: 2.5em; display: block; margin: auto; box-sizing: border-box;'></span><br><b>You do not own the required NFT to view this content.</b><br><small>If you recently acquired the NFT, it may take up to 30 minutes for the transaction to post and be available.</small>");

    $selected_account = get_user_meta(get_current_user_id(), 'ethereum_address', true) ?? '';

    if(!$hasgate){
        return do_shortcode($content);
    }

    return fungate_has_membership( $selected_account, $attributes ) ? do_shortcode( $content ) : fungate_shortcode( $content, '<div class="fungate_fail_message">' . $fail_message . '</div>' );
}
add_shortcode('fungate', 'fungate_membership_shortcode');
add_shortcode('fungate_inner', 'fungate_membership_shortcode');