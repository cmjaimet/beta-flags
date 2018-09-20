<?php
namespace BetaFlags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class FlagAdmin {
	public $nonce_name = 'betaflagsnonce';

	function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_betaFlag_enable', array( $this, 'flag_enable' ) );
	}

	function admin_menu() {
		add_submenu_page( 'tools.php', 'Beta Flags', 'Beta Flags', 'manage_options', FF_TEXT_DOMAIN, array( $this, 'settings_page' ) );
	}

	/* Plugin styles and scripts
	-------------------------------------------------------- */
	function enqueue_scripts( $hook ) {
		if ( $hook !== 'tools_page_beta-flags' ) {
			return;
		}
		wp_register_style( 'beta-flags-styles', FF_PLUGIN_URL . '/assets/beta-flags.css', array(), '1.1.0', false );
		wp_enqueue_style( 'beta-flags-styles' );
		wp_register_script( 'beta-flags-scripts', FF_PLUGIN_URL . '/assets/beta-flags.js', array(), '1.1.0', false );
		wp_enqueue_script( 'beta-flags-scripts' );
	}

	function settings_page() {
		$nonce_value = wp_create_nonce( $this->nonce_name );
		// $screen = get_current_screen();
		// print_r( $screen );
		?>
		<div class="wrap">
		<h1><?php esc_html_e( 'Beta Flags', FF_TEXT_DOMAIN ); ?></h1>
		<hr>
		<div class="notice-container"></div>
		<input type="hidden" id="<?php echo esc_attr( $this->nonce_name ); ?>" value="<?php echo esc_attr( $nonce_value ); ?>" />
		<?php
		$this->list_flags(
			false,
			'Available beta flags',
			'Beta flags or toggles allow betas to easily be enabled for users to test in a more realistic environment.'
		);
		$this->list_flags(
			true,
			'Enforced beta flags',
			'Betas listed below are currently configured to be enforced by default by the developers. These are flags that will be removed from the website code soon.'
		);
		?>
		</div>
		<?php
	}

	function list_flags( $enforced = true, $title = '', $description = '' ) {
		$flags = BetaFlags::init()->get_flags( $enforced );
		if ( isset( $flags ) ) {
			?>
			<h2><?php esc_html_e( $title, FF_TEXT_DOMAIN ); ?></h2>
			<p><?php esc_html_e( $description, FF_TEXT_DOMAIN ); ?></p>
			<table class="widefat">
			<thead>
			<tr>
			<th class="check-column"></th>
			<th class="row-title"><?php esc_html_e( 'Beta', FF_TEXT_DOMAIN ); ?></th>
			<th><?php esc_html_e( 'Key', FF_TEXT_DOMAIN ); ?></th>
			<th><?php esc_html_e( 'Author', FF_TEXT_DOMAIN ); ?></th>
			<th><?php esc_html_e( 'A/B Label', FF_TEXT_DOMAIN ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ( $flags as $key => $flag ) {
				$flag_key = $flag->get( 'key' );
				$enabled = $flag->get( 'enabled' );
				$class = ( $key % 2 == 0 ? 'alternate' : '' );
				?>
				<tr class="<?php echo esc_attr( $class ); ?>">
				<td><?php $this->show_flag_icon( $flag_key, $enforced, $enabled ); ?></td>
				<td class="row-title"><?php echo esc_html( $flag->get( 'title' ) ); ?></td>
				<td><pre><?php echo esc_attr( $flag_key ); ?></pre></td>
				<td><?php echo esc_html( $flag->get( 'author' ) ); ?></td>
				<td><?php echo esc_html( $flag->get( 'ab_label' ) ); ?></td>
				</td>
				</tr>
				<tr class="<?php echo esc_attr( $class ); ?>">
				<td></td>
				<td colspan="4"><?php echo esc_html( $flag->get( 'description' ) ); ?></td>
				</tr>
				<?php
			}
			?>
			</tbody>
			</table>
			<?php
		}
	}

	function show_flag_icon( $flag_key, $enforced, $enabled ) {
		$class = '';
		if ( true === $enforced ) {
			$class = 'status-marker-enforced';
		} elseif ( true === $enabled ) {
			$class = 'status-marker-enabled';
		}
		?>
		<span class="status-marker <?php echo esc_attr( $class ); ?>" id="beta-flag-<?php echo esc_attr( $flag_key ); ?>"></span>
		<?php
	}

	/**
	 * AJAX Action toggling betas from the WP admin area.
	 */
	function flag_enable( $test = false ) {
		$flag_validate = $this->flag_validate();
		if ( '' !== $flag_validate ) {
			return __( $flag_validate );
		}
		$response = array();
		$beta_key = trim( $_POST['betaKey'] );
    $response['key'] = $beta_key;
    $response['state'] = BetaFlags::init()->toggle_beta( $beta_key );
		if ( true === $test ) {
			echo json_encode( $response );
		} else {
			header( "Content-Type: application/json" );
			echo json_encode( $response );
			exit(); // Don't forget to always exit in the ajax function.
		}
	}

	function flag_validate() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return __( 'You are not authorized to perform that action (E451)', FF_TEXT_DOMAIN );
		}
		if ( isset( $_POST[ $this->nonce_name ] ) ) {
			$nonce_value = $_POST[ $this->nonce_name ];
		} else {
			return __( 'You are not authorized to perform that action (E353)', FF_TEXT_DOMAIN );
		}
		if ( ! wp_verify_nonce( $nonce_value, $this->nonce_name ) ) {
			return __( 'You are not authorized to perform that action (E314)', FF_TEXT_DOMAIN );
		}
		if ( ! isset( $_POST['betaKey'] ) ) {
			return __( 'No beta key', FF_TEXT_DOMAIN );
		}
		if ( empty( trim( $_POST['betaKey'] ) ) ) {
			return __( 'Beta key is blank', FF_TEXT_DOMAIN );
		}
		return '';
	}

}

new FlagAdmin();
