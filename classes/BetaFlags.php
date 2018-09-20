<?php
namespace BetaFlags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class BetaFlags {
  private static $instance;
	public $admin_notice = '';
	public $admin_trace = '';
	public $example;
	public $flags = []; // array of Flag objects
	public $flag_settings = []; // array of Flag enabled/disabled settings

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
		$this->flag_settings = $this->get_flag_settings();
	}

  /**
   * Add a new flag to the plugin register.
   *
   * @param array $flag
   * @return void
   */
	function add_flag( $args ) {
		$args = $this->add_flag_validate( $args );
		if ( ! empty( $args['key'] ) ) {
			$args['enabled'] = ( isset( $this->flag_settings[ $args['key'] ] ) ) ? true : false;
			$this->flags[] = new Flag( $args );
		} else {
			$trace = wp_debug_backtrace_summary( null, 0, false );
			$trace_flag = array_search( 'register_beta_flag' , $trace, true );
			$trace_partial = implode( ', ', $trace );
			if ( false !== $trace_flag ) {
				$pos = $trace_flag + 1;
				if ( isset( $trace[ $pos ] ) ) {
					$trace_partial = $trace[ $pos ];
				}
			}
			$this->admin_notice = ( is_string( $args ) ) ? $args : '';
			$this->admin_trace = __( 'TRACE: ', FF_TEXT_DOMAIN ) . $trace_partial;
			add_action( 'admin_notices', array( $this, 'add_flag_notice' ) );
		}
	}

	function add_flag_notice()  {
		$message = __( 'ERROR: ', FF_TEXT_DOMAIN );
		echo '<div class="notice notice-error"><p>' . esc_html( $message );
		echo esc_html( $this->admin_notice ) . '<br />';
		echo esc_html( $this->admin_trace );
		echo '</p></div>';
	}

	function add_flag_validate( $args ) {
		$defaults = array(
			'title' => __( 'No Name', FF_TEXT_DOMAIN ),
			'key' => '',
			'ab_label' => '',
	    'enforced' => false,
			'description' => '',
			'author' => '',
	  );
	  $args = wp_parse_args( $args, $defaults );
		// key must be unique
		if ( $this->is_key_duplicate( $args['key'] ) ) {
			return __( 'Key exists already', FF_TEXT_DOMAIN );
		}
		if ( preg_replace( "/[a-z0-9\-\_]+/", '', $args['key'] ) !== '' ) {
			return __( 'Key can only contain lowercase letter, numbers, hyphens, and underscores', FF_TEXT_DOMAIN );
		}
		if ( '' === $args['key'] ) {
			return __( 'You must supply a key', FF_TEXT_DOMAIN );
		}
		if ( preg_replace( "/[a-z0-9\-\_]+/", '', $args['ab_label'] ) !== '' ) {
			return __( 'A/B Labels can only contain lowercase letter, numbers, hyphens, and underscores', FF_TEXT_DOMAIN );
		}
		if ( '' === trim( $args['title'] ) ) {
			$args['title'] = __( 'No Name', FF_TEXT_DOMAIN );
		}
		// whitelabel boolean
		$args['enforced'] = ( true === $args['enforced'] ) ? true : false;
		return $args;
	}

	function is_key_duplicate( $key ) {
		$key = trim( $key );
		$key_exists = ( false === $this->find_flag( $key ) ) ? false : true;
		return $key_exists;
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
   * Undocumented function
   *
   * @param boolean $enforced
   * @return string|void All available flags if $enforced is false, else only returns 'enforced' betas.
   */
  function get_flags( $enforced = false ) {
    $flags = $this->flags;
    if ( $enforced ) {
      $filteredFlags = array_filter( $flags, function( $value ) {
        return $value->get( 'enforced' );
      } );
    } else {
      $filteredFlags = array_filter( $flags, function( $value ) {
        return ! $value->get( 'enforced' );
      } );
    }
    return $filteredFlags;
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
   * Toggle the beta for the current user.
   *
   * @return void
   */
  function toggle_beta( $flag_key ) {
    if ( isset( $this->flag_settings[ $flag_key ] ) ) {
      unset( $this->flag_settings[ $flag_key ] );
			$state = false;
    } else {
      $this->flag_settings[ $flag_key ] = true;
			$state = true;
    }
		$this->set_flag_settings();
		return $state;
  }

	function set_flag_settings() {
		update_option( FF_TEXT_DOMAIN, $this->flag_settings );
  }

	function get_flag_settings() {
    return get_option( FF_TEXT_DOMAIN, null );
  }

	// two sets of data: registered flags, flags on/off

}
