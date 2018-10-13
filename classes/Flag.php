<?php
namespace BetaFlags;

/**
* Flag Class
* Used for creating beta flags.
*
*/
class Flag {
	public $data; // key, title, description, author, ab_label, enforced, enabled

	/**
	* Create a new Flag object
	* Requires validation
	*/
	function __construct( $args ) {
		$this->data = $args;
	}

	/**
   * Display or retrieve a data element
   *
   * @param string $key The key for the data element
   * @return mixed The value of the data element
   */
	function get( $key ) {
		return $this->data[ $key ];
	}

	/**
	* Determine if a flag is active, i.e. if code is checking whether the flag should block it's execution or not
	*
	* @return bool Flag is active/not
	*/
  function is_active() {
		// call from Flag
    if ( true === $this->get( 'enforced' ) ) {
      return true; // ALLOW: flag has been removed so allow access to the beta
    }
		if ( false === $this->get( 'enabled' ) ) {
			return false; // BLOCK: flag disabled so block access to the beta or return false in admin
		}
		if ( is_admin() ) {
			return true; // ALLOW: flag enabled and this is admin settings page so show as is - has returned false already if disabled
		}
		return $this->is_ab_active(); // this is the front end and the flag is enabled so return false only if the page is AB testing
	}

	/**
	* Determine if a flag is activated by AB Testing parameters
	*
	* @return bool Flag is active/not
	*/
  function is_ab_active() {
		$ab_label = \BetaFlags::$ab_key;
		$ab_value = get_query_var( $ab_label, null );
		if ( is_null( $ab_value ) ) {
			return true; // ALLOW: flag enabled and no A/B test parameter in query string so allow access to the beta
		} else {
			return false; // BLOCK: flag enabled but A/B test parameter exists in query string so block access to the beta
		}
  }


}
