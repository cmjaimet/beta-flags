<?php
namespace BetaFlags;

class BetaFlags {
	public $flaglist;
	public $ab_key = 'ab';
	public $flag_settings;

	function __construct() {
		$this->flag_settings = get_option( FF_TEXT_DOMAIN, null );
		add_filter( 'query_vars', array( $this, 'query_vars_filter' ) );
		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {
		add_filter( 'post_link', array( $this, 'abtest_query_string' ), 10, 3 );
		add_filter( 'term_link', array( $this, 'abtest_query_string' ), 10, 3 );
	}

	function is_enabled( $flag_key ) {
    $flag = $this->flag_settings->flags[ $flag_key ];
		if ( false === $flag ) {
			return true; // if the flag doesn't exist then don't let it block code execution
		}
		if ( 1 === intval( $flag['enabled'] ) ) {
			return $this->is_ab_active(); // flag is enabled so check A/B test status
		} else {
			return true; // flag is disabled in admin
		}
  }

	function is_ab_active() {
		$ab_value = get_query_var( $this->ab_key, null );
		if ( is_null( $ab_value ) ) {
			return true; // ALLOW: flag enabled and no A/B test parameter in query string so allow access to the beta
		} else {
			return false; // BLOCK: flag enabled but A/B test parameter exists in query string so block access to the beta
		}
	}

	function abtest_query_string( $url, $post, $leavename=false ) {
		if ( 1 === $this->flag_settings->ab_test_on ) {
			if ( 1 === rand( 0, 1 ) ) {
				$url = add_query_arg( $this->ab_key, '1', $url );
			}
		}
		return $url;
	}

	function query_vars_filter( $vars ) {
		$vars[] = $this->ab_key;
		return $vars;
	}

}
