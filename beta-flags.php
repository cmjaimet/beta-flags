<?php
/**
*
* @package   Beta Flags
* @author    Charles Jaimet
* @link      https://github.com/cmjaimet
*
* @wordpress-plugin
* Plugin Name:       Beta Flags
* Description:       Insert beta flags to activate/deactivate new features, and to A/B test them.
* Version:           1.3.0
* Author:            Charles Jaimet
* Author URI:        https://github.com/cmjaimet
*
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'BETA_FLAGS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

include_once BETA_FLAGS_PLUGIN_PATH . 'classes/class-betaflags.php';
include_once BETA_FLAGS_PLUGIN_PATH . 'classes/class-admin.php';

$beta_flags_object = new \BetaFlags\BetaFlags();
new \BetaFlags\Admin();

function beta_flag_enabled( $slug ) {
	global $beta_flags_object;
	return $beta_flags_object->is_enabled( $slug );
}
