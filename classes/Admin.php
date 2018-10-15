<?php
namespace BetaFlags;

class Admin {
	public $nonce_name = 'betaflagsnonce';
	private $flag_data = array();
	private $beta_flags;

	function __construct() {
		global $beta_flags;
		$this->beta_flags = $beta_flags;
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	* Set menu items in the admin interface
	*
	* @return null
	*/
	function admin_menu() {
		add_submenu_page( 'tools.php', 'Beta Flags', 'Beta Flags', 'manage_options', FF_TEXT_DOMAIN, array( $this, 'settings_page' ) );
	}

	/**
	* Display the settings page for managing beta flags
	*
	* @return null
	*/
	function settings_page() {
		?>
		<h1><?php esc_html_e( 'Beta Flags', FF_TEXT_DOMAIN ); ?></h1>
		<hr>
		<?php
		$flag_data = $this->get_flag_data();
		if ( true === is_string( $flag_data ) ) {
			echo '<div id="message" class="updated fade">' . esc_html( $flag_data ) . '</div>';
			return;
		}
		$message = $this->form_submit();
		$enable_beta_testing = $this->beta_flags->flag_settings->ab_test_on;
		if ( '' !== $message ) {
			echo '<div id="message" class="updated fade">' . esc_html( $message ) . '</div>';
		}
		?>
		<div class="wrap">
		<form method="post" action="/wp-admin/tools.php?page=beta-flags">
		<div class="notice-container"></div>
		<?php
		wp_nonce_field( $this->nonce_name, $this->nonce_name, true, true );
		$this->flag_data = $flag_data;
		$this->list_flags();
		?>
		<p>
		<input type="checkbox" name="ab_test_on" value="1" <?php echo checked( $enable_beta_testing, 1, true ); ?> />
		<?php esc_html_e( 'Enable beta testing', FF_TEXT_DOMAIN ); ?>
		</p>
		<?php submit_button(); ?>
		</form>
		</div>
		<?php
	}

	/**
	* Display the list of beta flags for the form
	*
	* @return null
	*/
	function list_flags() {
		?>
		<table class="widefat">
		<thead>
		<tr>
		<th><?php esc_html_e( 'Enabled', FF_TEXT_DOMAIN ); ?></th>
		<th><?php esc_html_e( 'Title', FF_TEXT_DOMAIN ); ?></th>
		<th><?php esc_html_e( 'Key', FF_TEXT_DOMAIN ); ?></th>
		<th><?php esc_html_e( 'Author', FF_TEXT_DOMAIN ); ?></th>
		<th><?php esc_html_e( 'A/B Test', FF_TEXT_DOMAIN ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		$count = 0;
		foreach ( $this->flag_data as $flag_key => $flag ) {
			$enabled = $this->get_flag_setting( $flag_key, 'enabled' );
			$ab_test = $this->get_flag_setting( $flag_key, 'ab_test' );
			$class = ( $count % 2 == 0 ? 'alternate' : '' );
			?>
			<input type="hidden" name="flags[<?php echo esc_attr( $flag_key ); ?>][exists]" value="1" />
			<tr class="<?php echo esc_attr( $class ); ?>">
			<td><input type="checkbox" name="flags[<?php echo esc_attr( $flag_key ); ?>][enabled]" value="1" <?php checked( $enabled, 1, true ); ?> /></td>
			<td><?php echo esc_html( isset( $flag->title ) ? $flag->title : '' ); ?></td>
			<td><?php echo esc_attr( $flag_key ); ?></td>
			<td><?php echo esc_html( isset( $flag->author ) ? $flag->author : '' ); ?></td>
			<td><input type="checkbox" name="flags[<?php echo esc_attr( $flag_key ); ?>][ab_test]" value="1" <?php checked( $ab_test, 1, true ); ?> /></td>
			</td>
			</tr>
			<tr class="<?php echo esc_attr( $class ); ?>">
			<td></td>
			<td colspan="4"><?php echo esc_html( isset( $flag->description ) ? $flag->description : '' ); ?></td>
			</tr>
			<?php
			$count ++;
		}
		?>
		</tbody>
		</table>
		<?php
	}

	/**
	* Look up, validate, and return one property from a flag
	* @param string $flag_key The unique key identifying a beta flag
	* @param string $prop The name of the property to be retrieved
	*
	* @return mixed The value of the flag's property
	*/
	function get_flag_setting( $flag_key, $prop ) {
		if ( true === isset( $this->beta_flags->flag_settings->flags[ $flag_key ][ $prop ] ) ) {
			return $this->beta_flags->flag_settings->flags[ $flag_key ][ $prop ];
		}
		return null;
	}

	/**
	* Accept form submission, validate data, store in option, retain for use on page, return success message
	*
	* @return string Success or failure message on save process
	*/
	function form_submit() {
		$message = '';
		if ( isset( $_POST["submit"] ) ) {
			$message = $this->form_validate();
			if ( '' !== $message ) {
				return $message;
			}
			$settings = new \stdClass;
			$settings->ab_test_on = isset( $_POST['ab_test_on'] ) ? 1 : 0;
			$settings->flags = array();
			if ( isset( $_POST['flags'] ) ) {
				foreach ( $_POST['flags'] as $flag_key => $val ) {
					$settings->flags[ trim( $flag_key ) ] = array(
						'enabled' => ( isset( $val['enabled'] ) ? 1 : 0 ),
						'ab_test' => ( isset( $val['ab_test'] ) ? 1 : 0 )
					);
				}
			}
			update_option( FF_TEXT_DOMAIN, $settings );
			$this->beta_flags->flag_settings = $settings;
			$message = __( 'Beta flags successfully updated', FF_TEXT_DOMAIN );
		}
		return $message;
	}

	/**
	* Validate form submission: permission and nonce
	*
	* @return string Error message or blank on success
	*/
	function form_validate() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return __( 'You are not authorized to perform that action (E451)', FF_TEXT_DOMAIN );
		}
		if ( ! isset( $_POST[ $this->nonce_name ] ) ) {
			return __( 'You are not authorized to perform that action (E353)', FF_TEXT_DOMAIN );
		}
		$nonce_value = $_POST[ $this->nonce_name ];
		if ( ! wp_verify_nonce( $nonce_value, $this->nonce_name ) ) {
			return __( 'You are not authorized to perform that action (E314)', FF_TEXT_DOMAIN );
		}
		return '';
	}

	/**
	* Retrieve list of available beta flags from JSON file in theme or plugin (fallback)
	*
	* @return string|object Returns error message if no config file found or file does not contain valid JSON
	*/
	function get_flag_data() {
		$json_file = get_template_directory() . '/beta-flags.json';
		if ( file_exists ( $json_file ) ) {
			$flag_json = file_get_contents( $json_file );
		} else {
			$json_file = FF_PLUGIN_PATH . 'data/beta-flags.json';
			if ( file_exists ( $json_file ) ) {
				$flag_json = file_get_contents( $json_file );
			}
		}
		if ( false === $flag_json ) {
			return __( 'No configuration file found', FF_PLUGIN_PATH );
		}
		$flag_data = json_decode( $flag_json );
		if ( is_null( $flag_data ) ) {
			return __( 'Configuration file does not contain valid JSON', FF_PLUGIN_PATH );
		}
		if ( ! isset( $flag_data->flags ) ) {
			return __( 'No beta flag data found in configuration file', FF_PLUGIN_PATH );
		}
		return $flag_data->flags;
  }

}
