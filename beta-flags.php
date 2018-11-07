<?php
/**
 *
 * @package   Beta Flags
 * @author    Charles Jaimet
 * @link      https://github.com/cmjaimet
 *
 * @wordpress-plugin
 * Plugin Name:        Beta Flags
 * Description:        Insert beta flags to activate/deactivate new features, and to A/B test them.
 * Version:            1.3.0
 * Author:             Charles Jaimet
 * Author URI:         https://github.com/cmjaimet
 * Plugin URI:         https://wordpress.org/plugins/beta-flags/
 * Text Domain:        beta-flags
 * Domain Path:        /languages
 * Requires at least:  3.0
 * Tested up to:       4.9.8
 * License:            GPLv2 and up
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'BETA_FLAGS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

require_once BETA_FLAGS_PLUGIN_PATH . 'classes/class-betaflags.php';
require_once BETA_FLAGS_PLUGIN_PATH . 'classes/class-admin.php';

new \BetaFlags\Admin();

function beta_flag_enabled( $slug ) {
	return \BetaFlags\BetaFlags::get_instance()->is_enabled( $slug );
}
