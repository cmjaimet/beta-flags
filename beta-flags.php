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

// include_once FF_PLUGIN_PATH . 'classes/FlagList.php';
// include_once FF_PLUGIN_PATH . 'classes/Flag.php';
include_once FF_PLUGIN_PATH . 'classes/Admin.php';

class BetaFlags {
	public $flaglist;
	public static $ab_key = 'ab';
	public static $enable_beta_testing = 0;
	public $flag_settings;

	function __construct() {
		$this->flag_settings = get_option( FF_TEXT_DOMAIN, null );
		new BetaFlags\Admin();
		self::$enable_beta_testing = $this->flag_settings->ab_test_on;
		add_filter( 'query_vars', array( $this, 'query_vars_filter' ) );
		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {
		add_filter( 'post_link', array( $this, 'abtest_query_string' ), 10, 3 );
		add_filter( 'term_link', array( $this, 'abtest_query_string' ), 10, 3 );
	}

	function find_flag( $key ) {
		return $this->flag_settings->flags[ $key ];
  }

	function is_active( $flag_key ) {
    $flag = $this->find_flag( $flag_key );
		if ( false !== $flag ) {
			return ( 1 === intval( $flag['active'] ) ) ? true : false;
		} else {
			return true; // if the flag doesn't exist then don't let it block code execution
		}
  }

	function abtest_query_string( $url, $post, $leavename=false ) {
		if ( 1 === self::$enable_beta_testing ) {
			if ( 1 === rand( 0, 1 ) ) {
				$url = add_query_arg( self::$ab_key, '1', $url );
			}
		}
		return $url;
	}

	function query_vars_filter( $vars ) {
		$vars[] = self::$ab_key;
		return $vars;
	}

}
$beta_flags = new BetaFlags();

function beta_flag_is_active( $slug ) {
	global $beta_flags;
	return $beta_flags->is_active( $slug );
}
