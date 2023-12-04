<?php
/*
Plugin Name: Fungate
Plugin URI: https://fungate.dev
Description: Token gate content with Loopring NFTs
Version: 1.2
Author: Stephen Swanson
Author URI: https://swantech.us
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Important Notes!
// requires "sudo apt get phpx.x-gmp" installed on server. replace x.x with your php version (ex 8.1)

// Prevent direct access to this.
defined('ABSPATH') or die('No script kiddies please!');

// Include all the core files
include_once( 'core/walletconnect.php' );
include_once( 'core/admin.php' ); // admin settings pages
include_once( 'core/shortcodes.php' );
include_once( 'core/activation-hooks.php' );
include_once( 'core/protected-media.php' );
include_once( 'core/javascript-endpoints.php' );
include_once( 'core/logout-user.php' );
include_once( 'core/user-dashboard.php' );
include_once( 'core/gating-functions.php' );

// Features
include_once( 'core/integrations.php' ); // Add integrations to other plugins in the core/integrations.php file
include_once( 'core/nft-roles.php' );
include_once( 'core/fungate-post.php' );

