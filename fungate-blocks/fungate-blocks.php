<?php
defined('ABSPATH') or die('No script kiddies please!');
function fungate_blocks_init() {
	register_block_type(__DIR__ . '/fungate-block/build', array());
	register_block_type(__DIR__ . '/fungate-account-block/build', array());
	register_block_type(__DIR__ . '/fungate-media-block/build', array());
}
add_action( 'init', 'fungate_blocks_init' );
