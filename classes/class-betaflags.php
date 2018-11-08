<?php
namespace BetaFlags;

/**
* PROBLEM: URLs with query strings are automatically exempt from Batcache
*/
class BetaFlags {
	public $flaglist;
	public $flag_settings;

	public static function get_instance() {
		static $instance = null;
		if ( $instance === null ) {
				$instance = new BetaFlags();
		}
		return $instance;
	}

	function __construct() {
		$this->flag_settings = get_option( 'beta-flags', null );
	}

	/**
	* Determine if a flag is enabled or not for use on front end
	* If the flag has been enabled, a second step checks A/B testing status
	* By default, beta flags are set to block code execution unless specifically activated
	* @param string $flag_key The unique identifier for a flag
	*
	* @return bool Whether the code managed by the flag should be executed or not
	*/
	function is_enabled( $flag_key ) {
		if ( ! isset( $this->flag_settings->flags[ $flag_key ] ) ) {
			return false; // flag doesn't exist so block code execution
		}
		$flag = $this->flag_settings->flags[ $flag_key ];
		if ( ! isset( $flag['enabled'] ) ) {
			return false; // enabled flag not set so block code execution
		}
		return ( 1 === intval( $flag['enabled'] ) ) ? true : false;
	}

}
