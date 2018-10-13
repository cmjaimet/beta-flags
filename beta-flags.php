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

define( 'FF_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'FF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FF_TEXT_DOMAIN', 'beta-flags' );

include_once FF_PLUGIN_PATH . 'classes/BetaFlags.php';
include_once FF_PLUGIN_PATH . 'classes/Admin.php';

$beta_flags = new \BetaFlags\BetaFlags();

function beta_flag_is_active( $slug ) {
	global $beta_flags;
	return $beta_flags->is_active( $slug );
}
