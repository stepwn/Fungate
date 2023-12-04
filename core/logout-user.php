<?php

// AJAX action for logging out
function web3modal_logout() {
    // Check if the current user is not an admin
    if ( !current_user_can('administrator') ) {
        wp_logout();
        echo true;
    } else {
        echo false;
    }

    wp_die(); // This is required to terminate immediately and return a proper response
}

add_action('wp_ajax_web3modal_logout', 'web3modal_logout');
add_action('wp_ajax_nopriv_web3modal_logout', 'web3modal_logout');
