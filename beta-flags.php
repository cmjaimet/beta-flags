<?php
/**
 *
 * @package   Beta Flags
 * @author    Charles Jaimet
 * @link      https://github.com/cmjaimet
 *
 * @wordpress-plugin
 * Plugin Name:       Beta Flags
 * Description:       Based on Beta Flags by James Williams (https://jamesrwilliams.co.uk/)
 * Version:           1.2.2
 * Author:            Charles Jaimet
 * Author URI:        https://github.com/cmjaimet
 *
 */

/* If this file is called directly abort.
-------------------------------------------------------- */
if ( ! defined( 'WPINC' ) ) {
	wp_die();
}

/* Define plugin paths and url for global usage
-------------------------------------------------------- */
define( 'FF_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'FF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FF_TEXT_DOMAIN', 'beta-flags' );

/* On plugin activation
-------------------------------------------------------- */
register_activation_hook( __FILE__, function() {} );

/* Includes
-------------------------------------------------------- */
include_once FF_PLUGIN_PATH . 'classes/BetaFlags.php';
include_once FF_PLUGIN_PATH . 'classes/Flag.php';
include_once FF_PLUGIN_PATH . 'classes/Admin.php';
include_once FF_PLUGIN_PATH . 'config-flags.php';

add_filter( 'query_vars', 'pm_betaflag_query_vars_filter' );
function pm_betaflag_query_vars_filter( $vars ) {
	$flags = \BetaFlags\BetaFlags::init()->flags;
	foreach ( $flags as $flag ) {
		$vars[] = $flag->get( 'ab_label' );
	}
	return $vars;
}

/**
 * Register a beta flag with the plugin.
 *
 * @param [Array] $args
 * @return void
 */
function pm_betaflag_register( $args ) {
	\BetaFlags\BetaFlags::init()->add_flag( $args );
}

function pm_betaflag_is_active( $betaKey = '' ) {
  return \BetaFlags\BetaFlags::init()->is_active( $betaKey );
}
