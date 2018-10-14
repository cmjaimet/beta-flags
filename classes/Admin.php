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

	function admin_menu() {
		add_submenu_page( 'tools.php', 'Beta Flags', 'Beta Flags', 'manage_options', FF_TEXT_DOMAIN, array( $this, 'settings_page' ) );
	}

	function settings_page() {
		global $beta_flags;
		$this->form_submit();
		$enable_beta_testing = $beta_flags->flag_settings->ab_test_on;
		?>
		<div class="wrap">
		<form method="post" action="/wp-admin/tools.php?page=beta-flags">
		<h1><?php esc_html_e( 'Beta Flags', FF_TEXT_DOMAIN ); ?></h1>
		<hr>
		<div class="notice-container"></div>
		<?php
		wp_nonce_field( $this->nonce_name, $this->nonce_name, true, true );
		$this->flag_data = $this->get_flag_data();
		$this->list_flags(
			__( 'Available beta flags', FF_TEXT_DOMAIN ),
			__( 'Beta flags or toggles allow betas to easily be enabled for users to test in a more realistic environment.', FF_TEXT_DOMAIN )
		);
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

	function list_flags( $title = '', $description = '' ) {
		global $beta_flags;
		?>
		<h2><?php esc_html( $title ); ?></h2>
		<p><?php esc_html( $description ); ?></p>
		<table class="widefat">
		<thead>
		<tr>
		<th><?php esc_html_e( 'Active', FF_TEXT_DOMAIN ); ?></th>
		<th><?php esc_html_e( 'Title', FF_TEXT_DOMAIN ); ?></th>
		<th><?php esc_html_e( 'Key', FF_TEXT_DOMAIN ); ?></th>
		<th><?php esc_html_e( 'Author', FF_TEXT_DOMAIN ); ?></th>
		<th><?php esc_html_e( 'A/B Test', FF_TEXT_DOMAIN ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ( $this->flag_data as $key => $flag ) {
			$enabled = $beta_flags->flag_settings->flags[ $key ]['enabled'];
			$ab_test = $beta_flags->flag_settings->flags[ $key ]['ab_test'];
			$class = ( $key % 2 == 0 ? 'alternate' : '' );
			?>
			<input type="hidden" name="flags[<?php echo esc_attr( $key ); ?>][exists]" value="1" />
			<tr class="<?php echo esc_attr( $class ); ?>">
			<td><input type="checkbox" name="flags[<?php echo esc_attr( $key ); ?>][enabled]" value="1" <?php checked( $enabled, 1, true ); ?> /></td>
			<td><?php echo esc_html( $flag->title ); ?></td>
			<td><?php echo esc_attr( $key ); ?></td>
			<td><?php echo esc_html( $flag->author ); ?></td>
			<td><input type="checkbox" name="flags[<?php echo esc_attr( $key ); ?>][ab_test]" value="1" <?php checked( $ab_test, 1, true ); ?> /></td>
			</td>
			</tr>
			<tr class="<?php echo esc_attr( $class ); ?>">
			<td></td>
			<td colspan="4"><?php echo esc_html( $flag->description ); ?></td>
			</tr>
			<?php
		}
		?>
		</tbody>
		</table>
		<?php
	}

	function form_submit() {
		global $beta_flags;
		if ( isset( $_POST["submit"] ) ) {
			$message = $this->form_validate();
			if ( '' !== $message ) {
				echo '<p>' . esc_html( $message ) . '</p>';
				return false;
			}
			$settings = new \stdClass;
			$settings->ab_test_on = isset( $_POST['ab_test_on'] ) ? 1 : 0;
			$settings->flags = array();
			if ( isset( $_POST['flags'] ) ) {
				foreach ( $_POST['flags'] as $key => $val ) {
					$settings->flags[ trim( $key ) ] = array(
						'enabled' => ( isset( $val['enabled'] ) ? 1 : 0 ),
						'ab_test' => ( isset( $val['ab_test'] ) ? 1 : 0 )
					);
				}
			}
			update_option( FF_TEXT_DOMAIN, $settings );
			$beta_flags->flag_settings = $settings;
		}
	}

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

	function get_flag_data() {
		$flag_json = file_get_contents( get_template_directory() . '/beta-flags.json' );
		if ( false === $flag_json ) {
			$flag_json = file_get_contents( FF_PLUGIN_PATH . 'config/beta-flags.json' );
		}
		if ( false === $flag_json ) {
			return null;
		}
		$flag_data = json_decode( $flag_json );
		if ( is_null( $flag_data ) ) {
			return null;
		}
		if ( ! isset( $flag_data->flags ) ) {
			return null;
		}
		return $flag_data->flags;
  }

}
