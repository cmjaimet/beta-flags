<?php
namespace BetaFlags;

class Admin {
	public $nonce_name = 'betaflagsnonce';
	private $flag_data = array();
	private $beta_flags;
	private $domain = 'beta-flags';

	function __construct() {
		global $beta_flags_object;
		$this->beta_flags = $beta_flags_object;
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	* Set menu items in the admin interface
	*
	* @return null
	*/
	function admin_menu() {
		add_submenu_page( 'tools.php', 'Beta Flags', 'Beta Flags', 'manage_options', $this->domain, array( $this, 'settings_page' ) );
	}

	/**
	* Display the settings page for managing beta flags
	*
	* @return null
	*/
	function settings_page() {
		?>
		<h1><?php esc_html_e( 'Beta Flags', 'beta-flags' ); ?></h1>
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
		<?php esc_html_e( 'Enable beta testing', 'beta-flags' ); ?>
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
		<th><?php esc_html_e( 'Enabled', 'beta-flags' ); ?></th>
		<th><?php esc_html_e( 'Title', 'beta-flags' ); ?></th>
		<th><?php esc_html_e( 'Key', 'beta-flags' ); ?></th>
		<th><?php esc_html_e( 'Author', 'beta-flags' ); ?></th>
		<th><?php esc_html_e( 'A/B Test', 'beta-flags' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		$count = 0;
		foreach ( $this->flag_data as $flag_key => $flag ) {
			$enabled = $this->get_flag_setting( $flag_key, 'enabled' );
			$ab_test = $this->get_flag_setting( $flag_key, 'ab_test' );
			$class = ( 0 === $count % 2 ? 'alternate' : '' );
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
		if ( ! isset( $_POST['submit'] ) ) {
			return '';
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return __( 'You are not authorized to perform that action (E451)', 'beta-flags' );
		}
		if ( isset( $_POST[ $this->nonce_name ] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $this->nonce_name ] ) ), $this->nonce_name ) ) {
			return __( 'You are not authorized to perform that action (E314)', 'beta-flags' );
		}
		if ( ! isset( $_POST[ $this->nonce_name ] ) ) {
			return __( 'You are not authorized to perform that action (E353)', 'beta-flags' );
		}
		$settings = new \stdClass;
		$settings->ab_test_on = isset( $_POST['ab_test_on'] ) ? 1 : 0;
		$settings->flags = array();
		if ( isset( $_POST['flags'] ) ) {
			$flags = wp_unslash( $_POST['flags'] );
			if ( is_array( $flags ) ) {
				foreach ( $flags as $flag_key => $val ) {
					$settings->flags[ sanitize_text_field( $flag_key ) ] = array(
						'enabled' => ( isset( $val['enabled'] ) ? 1 : 0 ),
						'ab_test' => ( isset( $val['ab_test'] ) ? 1 : 0 ),
					);
				}
			}
		}
		update_option( $this->domain, $settings );
		$this->beta_flags->flag_settings = $settings;
		return __( 'Beta flags successfully updated', 'beta-flags' );
	}

	/**
	* Retrieve list of available beta flags from JSON file in theme or plugin (fallback)
	*
	* @return string|object Returns error message if no config file found or file does not contain valid JSON
	*/
	function get_flag_data() {
		$json_file = get_template_directory() . '/beta-flags.json';
		if ( file_exists( $json_file ) ) {
			$flag_json = $this->file_get_contents( $json_file );
		} else {
			$json_file = BETA_FLAGS_PLUGIN_PATH . 'data/beta-flags.json';
			if ( file_exists( $json_file ) ) {
				$flag_json = $this->file_get_contents( $json_file );
			}
		}
		if ( false === $flag_json ) {
			return __( 'No configuration file found', 'beta-flags' );
		}
		$flag_data = json_decode( $flag_json );
		if ( is_null( $flag_data ) ) {
			return __( 'Configuration file does not contain valid JSON', 'beta-flags' );
		}
		if ( ! isset( $flag_data->flags ) ) {
			return __( 'No beta flag data found in configuration file', 'beta-flags' );
		}
		return $flag_data->flags;
	}

	function file_get_contents( $path ) {
		if ( function_exists( 'wpcom_vip_file_get_contents' ) ) {
			return wpcom_vip_file_get_contents( $path );
		} else {
			return file_get_contents( $path ); // @codingStandardsIgnoreLine replaces VIP helper function where not available
		}
	}

}
