<?php
namespace BetaFlags;

class BetaFlags {
	public $flaglist;
	public $ab_key = 'ab';
	public $flag_settings;

	function __construct() {
		$this->flag_settings = get_option( FF_TEXT_DOMAIN, null );
		new Admin();
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
