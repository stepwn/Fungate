<?php
function fungate_block_register_block() {
    // Register the block editor script
    wp_register_script(
        'fungate-block-editor-script',
       	plugins_url('/js/fungate-block-editor.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor'),
        '1.0.0',
        true
    );

    // Register your block
    register_block_type('fungate/fungate-block', array(
        'editor_script' => 'fungate-block-editor-script',
    ));
}

add_action('init', 'fungate_block_register_block');
