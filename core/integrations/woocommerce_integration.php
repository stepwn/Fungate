<?php

// Add a new tab in the coupon data panel.
add_action('woocommerce_coupon_data_tabs', 'fungate_add_nft_coupon_data_tab', 10, 1);
function fungate_add_nft_coupon_data_tab($tabs) {
    $tabs['fungate'] = array(
        'label'   => __('Fungate', 'your-text-domain'),
        'target'  => 'nft_coupon_data',
        'class'   => '',
    );
    return $tabs;
}

// Add custom fields in the new tab.
add_action('woocommerce_coupon_data_panels', 'fungate_add_nft_coupon_data_panel');
function fungate_add_nft_coupon_data_panel() {
    ?>
    <div id="nft_coupon_data" class="panel woocommerce_options_panel">
        <?php
        // NFT Field
        woocommerce_wp_text_input(array(
            'id'          => 'nft',
            'label'       => __('NFT', 'your-text-domain'),
            'placeholder' => '',
            'desc_tip'    => true,
            'description' => __('Enter the required NFT logic for this coupon.', 'your-text-domain'),
        ));

        // Minter Field
        woocommerce_wp_text_input(array(
            'id'          => 'minter',
            'label'       => __('Minter', 'your-text-domain'),
            'placeholder' => '',
            'desc_tip'    => true,
            'description' => __('Enter the required Minter logic for this coupon.', 'your-text-domain'),
        ));

        // Contract Field
        woocommerce_wp_text_input(array(
            'id'          => 'contract',
            'label'       => __('Contract', 'your-text-domain'),
            'placeholder' => '',
            'desc_tip'    => true,
            'description' => __('Enter the required Contract logic for this coupon.', 'your-text-domain'),
        ));

        // Schedule Field
        woocommerce_wp_text_input(array(
            'id'          => 'scheudle',
            'label'       => __('schedule', 'your-text-domain'),
            'placeholder' => '',
            'desc_tip'    => true,
            'description' => __('Enter the schedule logic for this coupon.', 'your-text-domain'),
        ));
        ?>
    </div>
    <?php
}

// Save the custom fields data.
add_action('woocommerce_coupon_options_save', 'fungate_save_nft_coupon_data');
function fungate_save_nft_coupon_data($post_id) {
    // NFT Field
    $nft = isset($_POST['nft']) ? wc_clean($_POST['nft']) : '';
    update_post_meta($post_id, 'nft', $nft);

    // Minter Field
    $minter = isset($_POST['minter']) ? wc_clean($_POST['minter']) : '';
    update_post_meta($post_id, 'minter', $minter);

    // Contract Field
    $contract = isset($_POST['contract']) ? wc_clean($_POST['contract']) : '';
    update_post_meta($post_id, 'contract', $contract);
    
    // Schedule Field
    $schedule = isset($_POST['schedule']) ? wc_clean($_POST['schedule']) : '';
    update_post_meta($post_id, 'schedule', $schedule);
}

add_filter('woocommerce_product_data_tabs', 'fungate_add_custom_product_data_tab');
function fungate_add_custom_product_data_tab($tabs) {
    $tabs['fungate'] = array(
        'label' => __('Fungate', 'your-text-domain'),
        'target' => 'nft_product_data',
        'class' => array(),
    );
    return $tabs;
}

add_action('woocommerce_product_data_panels', 'fungate_add_custom_fields_to_product_tab');
function fungate_add_custom_fields_to_product_tab() {
    ?>
    <div id='nft_product_data' class='panel woocommerce_options_panel'>
        <?php
        echo '<div class="options_group">';

        // NFT Field
        woocommerce_wp_text_input(
            array(
                'id'          => '_nft',
                'label'       => __('NFT', 'your-text-domain'),
                'placeholder' => '',
                'desc_tip'    => true,
                'description' => __('Enter the NFT logic for this product.', 'your-text-domain'),
            )
        );

        // Minter Field
        woocommerce_wp_text_input(
            array(
                'id'          => '_minter',
                'label'       => __('Minter', 'your-text-domain'),
                'placeholder' => '',
                'desc_tip'    => true,
                'description' => __('Enter the Minter logic for this product.', 'your-text-domain'),
            )
        );

        // Contract Field
        woocommerce_wp_text_input(
            array(
                'id'          => '_contract',
                'label'       => __('Contract', 'your-text-domain'),
                'placeholder' => '',
                'desc_tip'    => true,
                'description' => __('Enter the Contract logic for this product.', 'your-text-domain'),
            )
        );

        // Contract Field
        woocommerce_wp_text_input(
            array(
                'id'          => '_schedule',
                'label'       => __('Schedule', 'your-text-domain'),
                'placeholder' => '',
                'desc_tip'    => true,
                'description' => __('Enter the Schedule logic for this product.', 'your-text-domain'),
            )
        );

        echo '</div>';
        ?>
    </div>
    <?php
}


add_action('woocommerce_process_product_meta', 'fungate_save_custom_product_fields');
function fungate_save_custom_product_fields($post_id) {
    // Save NFT Field
    $nft = isset($_POST['_nft']) ? wc_clean($_POST['_nft']) : '';
    update_post_meta($post_id, '_nft', $nft);

    // Save Minter Field
    $minter = isset($_POST['_minter']) ? wc_clean($_POST['_minter']) : '';
    update_post_meta($post_id, '_minter', $minter);

    // Save Contract Field
    $contract = isset($_POST['_contract']) ? wc_clean($_POST['_contract']) : '';
    update_post_meta($post_id, '_contract', $contract);

    // Save Schedule Field
    $schedule = isset($_POST['_schedule']) ? wc_clean($_POST['_schedule']) : '';
    update_post_meta($post_id, '_schedule', $schedule);
}

add_filter('woocommerce_add_to_cart_validation', 'fungate_validate_nft_before_add_to_cart', 10, 3);
function fungate_validate_nft_before_add_to_cart($passed, $product_id, $quantity) {
    // Fetch the product's NFT requirements.
    $required_nft = get_post_meta($product_id, '_nft', true);
    $required_minter = get_post_meta($product_id, '_minter', true);
    $required_contract = get_post_meta($product_id, '_contract', true);
    $required_schedule = get_post_meta($product_id, '_schedule', true);

    // If the product doesn't have NFT requirements, allow adding to cart.
    if (!$required_nft && !$required_minter && !$required_contract && !$required_schedule) {
        return $passed;
    }

    // Get the current user.
    $current_user = wp_get_current_user();

    // Fetch the user's NFTs from user meta (assuming it's stored as an array).
    $user_nfts = get_user_meta($current_user->ID, 'nfts', true);

    $has_access = false;

    // Check each user's NFT against the product's requirements.
    foreach ($user_nfts as $user_nft) {
        if (fungate_check_conditions($user_nft, array('minter' => $required_minter, 'nft' => $required_nft, 'contract' => $required_contract, 'schedule' => $required_schedule))) {
            $has_access = true;
            break;
        }
    }

    // If the user doesn't have the required NFT, prevent adding to cart and show an error message.
    if (!$has_access) {
        wc_add_notice(__('You do not have the required NFT to purchase this product.', 'your-text-domain'), 'error');
        $passed = false;
    }

    return $passed;
}

add_action('woocommerce_check_cart_items', 'fungate_validate_nft_before_checkout');
function fungate_validate_nft_before_checkout() {
    // Loop through cart items and check for NFT requirements.
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_id = $cart_item['product_id'];

        // Fetch the product's NFT requirements.
        $required_nft = get_post_meta($product_id, '_nft', true);
        $required_minter = get_post_meta($product_id, '_minter', true);
        $required_contract = get_post_meta($product_id, '_contract', true);
        $required_schedule = get_post_meta($product_id, '_schedule', true);

        // If the product doesn't have NFT requirements, skip to the next item.
        if (!$required_nft && !$required_minter && !$required_contract) {
            continue;
        }

        // Get the current user.
        $current_user = wp_get_current_user();

        // Fetch the user's NFTs from user meta (assuming it's stored as an array).
        $user_nfts = get_user_meta($current_user->ID, 'nfts', true);

        $has_access = false;

        // Check each user's NFT against the product's requirements.
        foreach ($user_nfts as $user_nft) {
            if (fungate_check_conditions($user_nft, array('minter' => $required_minter, 'nft' => $required_nft, 'contract' => $required_contract, 'schedule' => $required_schedule))) {
                $has_access = true;
                break;
            }
        }

        // If the user doesn't have the required NFT, prevent checkout and show an error message.
        if (!$has_access) {
            wc_add_notice(sprintf(__('You do not have the required NFT to purchase "%s".', 'your-text-domain'), get_the_title($product_id)), 'error');
            break;
        }
    }
}

add_action('woocommerce_check_cart_items', 'fungate_validate_nft_coupon_at_checkout');

function fungate_validate_nft_coupon_at_checkout() {
    // If there are no coupons, no need to validate.
    if ( ! WC()->cart->has_discount() ) return;

    // Get applied coupons
    $coupons = WC()->cart->get_applied_coupons();

    foreach ( $coupons as $code ) {
        $coupon = new WC_Coupon( $code );

        // Check if it's an NFT coupon by checking if it has NFT related meta data.
        $nft = get_post_meta( $coupon->get_id(), 'nft', true );
        $minter = get_post_meta( $coupon->get_id(), 'minter', true );
        $contract = get_post_meta( $coupon->get_id(), 'contract', true );
        $required_schedule = get_post_meta($product_id, '_schedule', true);

        // If it's not an NFT coupon, skip to the next coupon.
        if ( !$nft && !$minter && !$contract ) continue;

        $has_access = false;

        // Get the current user.
        $current_user = wp_get_current_user();

        // Fetch the user's NFTs from user meta (assuming it's stored as an array).
        $user_nfts = get_user_meta( $current_user->ID, 'nfts', true );

        // Check each user's NFT against the coupon's attributes.
        foreach ( $user_nfts as $user_nft ) {
            if ( fungate_check_conditions( $user_nft, array( 'minter' => $minter, 'nft' => $nft, 'contract' => $contract, 'schedule' => $required_schedule) ) ) {
                $has_access = true;
                break;
            }
        }

        // If the user doesn't have access, prevent checkout and show an error message.
        if ( !$has_access ) {
            wc_add_notice( sprintf( __( 'You do not have the required NFT to use the coupon "%s".', 'your-text-domain' ), $code ), 'error' );
        }
    }
}

function fungate_add_connect_wallet_option_to_woocommerce() {
    echo '<p class="web3-login-text">Log in with Web3</p>';
    echo esc_html(do_shortcode('[fungate contract="login"]wallet login[/fungate]'));
}

add_action('woocommerce_after_customer_login_form', 'fungate_add_connect_wallet_option_to_woocommerce');