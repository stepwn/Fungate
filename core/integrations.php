<?php
// Include integrated plugins
if ( get_option( 'fungate_buddypress_enabled' ) == 1 ) {
    include_once( 'integrations/buddypress_integration.php' );
}
if ( get_option( 'fungate_woocommerce_enabled' ) == 1 ) {
    include_once( 'integrations/woocommerce_integration.php' );
}
if ( get_option( 'fungate_tinymce_enabled' ) == 1 ) {
    include_once( 'integrations/tinymce.php' );
}