<?php
namespace BetaFlags;

class FlagList {
  private static $instance;
	// public $admin_notice = '';
	// public $admin_trace = '';
	// public $example;
	public $flags = []; // array of Flag objects
	public $flag_settings; // object of Flag enabled/disabled settings

  /**
   * Static function to create an instance if none exists
   */
  public static function init() {
    if ( is_null( self::$instance ) ) {
      self::$instance = new self();
    }
    return self::$instance;
  }

	function __construct() {
		$this->get_flag_list();
	}

  /**
   * Retrieve the flag object of a specified key.
   *
   * @param string $key
   * @return void
   */
  function find_flag( $key ) {
    $flag = false;
    $flags = $this->flags;
    foreach( $flags as $struct ) {
      if ( $key === $struct->data['key'] ) {
        $flag = $struct;
        break;
      }
    }
    return $flag;
  }

	/**
   * Get and register beta flags from JSON in the theme (fallback to plugin)
	 * need to validate the JSON and unit test properly
	 * cache this? option/transient?
   *
   * @return string|void All available flags if $enforced is false, else only returns 'enforced' betas.
   */
  function get_flag_list() {
		$this->flag_settings = get_option( FF_TEXT_DOMAIN, null );
	}

  /**
   * Check if the provided key is currently enabled.
   *
   * @param string $flagKey
   * @return boolean
   */
  function is_active( $flag_key ) {
    $flag = $this->find_flag( $flag_key );
		if ( false !== $flag ) {
			return $flag->is_active();
		} else {
			return true; // if the flag doesn't exist then don't let it block code execution
		}
  }

	/**
   * Undocumented function
   *
   * @param boolean $enforced
   * @return string|void All available flags if $enforced is false, else only returns 'enforced' betas.
   */
  // function list_flags( $enforced = false ) {
  //   $flags = $this->flags;
  //   if ( $enforced ) {
  //     $filteredFlags = array_filter( $flags, function( $value ) {
  //       return $value->get( 'enforced' );
  //     } );
  //   } else {
  //     $filteredFlags = array_filter( $flags, function( $value ) {
  //       return ! $value->get( 'enforced' );
  //     } );
  //   }
  //   return $filteredFlags;
  // }

}
