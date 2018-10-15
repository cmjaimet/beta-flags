<?php
namespace BetaFlags;

class BetaFlags {
	public $flaglist;
	public $ab_key = 'ab';
	public $flag_settings;

	function __construct() {
		$this->flag_settings = get_option( 'beta-flags', null );
		add_filter( 'query_vars', array( $this, 'query_vars_filter' ) );
		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {
		add_filter( 'post_link', array( $this, 'abtest_post_link' ), 10, 3 );
		add_filter( 'term_link', array( $this, 'abtest_term_link' ), 10, 3 );
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
		if ( false === isset( $this->flag_settings->flags[ $flag_key ] ) ) {
			return false; // flag doesn't exist so block code execution
		}
		$flag = $this->flag_settings->flags[ $flag_key ];
		if ( false === isset( $flag['enabled'] ) ) {
			return false; // enabled flag not set so block code execution
		}
		if ( 1 === intval( $flag['enabled'] ) ) {
			if ( false === isset( $flag['ab_test'] ) ) {
				return true; // ab_test flag not set so allow code execution
			}
			return $this->is_ab_active( $flag['ab_test'] ); // flag is enabled so check A/B test status
		} else {
			return false; // flag is disabled in admin
		}
	}

	/**
	* Determine from the A/B testing status the flag state for enabled flags
	* Return true if A/B testing for this flag has been disabled in the admin, otherwise check the URL query string
	* @param integer $ab_test_on Integer representation A/B test status for this flag
	*
	* @return bool Whether the code managed by the flag should be executed or not based on A/B testing criteria
	*/
	function is_ab_active( $flag_ab_test ) {
		if ( 0 === $this->flag_settings->ab_test_on ) {
			return true; // A/B testing has been disabled for the whole site
		}
		if ( 0 === $flag_ab_test ) {
			return true; // A/B testing has been disabled for this flag
		}
		$ab_value = get_query_var( $this->ab_key, null );
		if ( is_null( $ab_value ) ) {
			return true; // ALLOW: flag enabled and no A/B test parameter in query string so allow access to the beta
		} else {
			return false; // BLOCK: flag enabled but A/B test parameter exists in query string so block access to the beta
		}
	}

	/**
	* Add a query string to half of all URLs affected when A/B testing is enabled for the website
	* @param string $url The post URL
	* @param object $post The post object
	* @param string $leavename Whether to keep the post name or page name
	*
	* @return string The revised URL with or without the A/B Testing query string
	*/
	function abtest_post_link( $url, $post, $leavename = false ) {
		return $this->abtest_query_string( $url );
	}

	/**
	* Add a query string to half of all URLs affected when A/B testing is enabled for the website
	* @param string $url The term URL
	* @param object $post The term object
	* @param string $taxonomy The taxonomy slug
	*
	* @return string The revised URL with or without the A/B Testing query string
	*/
	function abtest_term_link( $url, $term, $taxonomy ) {
		return $this->abtest_query_string( $url );
	}

	/**
	* Add a query string to half of all URLs affected when A/B testing is enabled for the website
	* @param string $url The URL of the web page
	*
	* @return string The revised URL with or without the A/B Testing query string
	*/
	function abtest_query_string( $url ) {
		if ( 1 === $this->flag_settings->ab_test_on ) {
			if ( 1 === wp_rand( 0, 1 ) ) {
				$url = add_query_arg( $this->ab_key, '1', $url );
			}
		}
		return $url;
	}

	/**
	* Allow the A/B testing query string key to be passed to WordPress
	* @param array $vars The list of query string keys that can be passed to WordPress through the front end
	*
	* @return array The revised list of query string keys that can be passed to WordPress through the front end
	*/
	function query_vars_filter( $vars ) {
		$vars[] = $this->ab_key;
		return $vars;
	}

}
