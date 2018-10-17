<?php
/**
* Class BetaFlags
*
* @package BetaFlags
*/
namespace UnitTestBetaFlags;

class TestAdmin extends \WP_UnitTestCase {
	private $flag;
	private $admin;

	function setUp() {
		global $beta_flags;
		parent::setUp();
		$this->make_settings();
		$user_id = $this->make_user( 'administrator' );
		wp_set_current_user( $user_id );
		$beta_flags = new \BetaFlags\BetaFlags();
		$this->admin = new \BetaFlags\Admin();
	}

	function test_admin_menu() {
		$this->admin->admin_menu();
		$this->assertNotEmpty( menu_page_url( 'beta-flags', false ) );
	}

	function test_settings_page() {
		ob_start();
		$this->admin->settings_page();
		$output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<h1>Beta Flags</h1>', $output );
		$this->assertContains( 'sticky_video', $output );
		$nonce_value = wp_create_nonce( 'betaflagsnonce' );
		$this->assertContains( '<input type="hidden" id="betaflagsnonce" name="betaflagsnonce" value="' . $nonce_value . '" />', $output );
	}

	function test_list_flags() {
		ob_start();
		$this->admin->settings_page();
		$output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<th>Enabled</th>', $output );
		$this->assertContains( '<td><input type="checkbox" name="flags[sticky_video][ab_test]" value="1"  /></td>', $output );
	}

	function test_get_flag_setting() {
		$this->assertEquals( $this->admin->get_flag_setting( 'new_sidebar', 'ab_test' ), 0 );
		$this->assertEquals( $this->admin->get_flag_setting( 'new_sidebar', 'enabled' ), 1 );
		$this->assertEquals( $this->admin->get_flag_setting( 'sticky_video', 'ab_test' ), 0 );
		$this->assertEquals( $this->admin->get_flag_setting( 'sticky_video', 'enabled' ), 0 );
	}

	function test_form_submit() {
		$_POST = array();
		$_POST['ab_test_on'] = '1';
		$_POST['flags'] = array();
		$_POST['flags']['test_one'] = array(
			'enabled' => '1',
		);
		$_POST['flags']['test_two'] = array(
			'ab_test' => '1',
		);
		$result = $this->admin->form_submit();
		$this->assertEquals( '', $result );
		$_POST['submit'] = 'Save';
		$result = $this->admin->form_submit();
		$this->assertEquals( 'You are not authorized to perform that action (E353)', $result );
		$nonce_value = wp_create_nonce( 'betaflagsnonce' );
		$_POST['betaflagsnonce'] = $nonce_value;
		$result = $this->admin->form_submit();
		$settings = get_option( 'beta-flags' );
		$this->assertEquals( 'Beta flags successfully updated', $result );
		$this->assertEquals( $settings->ab_test_on, 1 );
		$this->assertEquals( $settings->flags['test_one']['enabled'], 1 );
		$this->assertEquals( $settings->flags['test_one']['ab_test'], 0 );
		$this->assertEquals( $settings->flags['test_two']['enabled'], 0 );
		$this->assertEquals( $settings->flags['test_two']['ab_test'], 1 );
	}

	function test_form_validate() {
		$_POST['submit'] = 'Save';
		$this->assertEquals( $this->admin->form_submit(), 'You are not authorized to perform that action (E353)' );
		$_POST['betaflagsnonce'] = 'baddata';
		$this->assertEquals( $this->admin->form_submit(), 'You are not authorized to perform that action (E314)' );
		$nonce_value = wp_create_nonce( 'betaflagsnonce' );
		$_POST['betaflagsnonce'] = $nonce_value;
		$this->assertEquals( 'Beta flags successfully updated', $this->admin->form_submit() );
		$user_id = $this->make_user( 'author' );
		wp_set_current_user( $user_id );
		$this->assertEquals( $this->admin->form_submit(), 'You are not authorized to perform that action (E451)' );
	}

	function test_get_flag_data() {
		$flag_data = $this->admin->get_flag_data();
		$this->assertEquals( 'Fred Page', $flag_data->redesign_v109->author );
		$file_plugin = FF_PLUGIN_PATH . 'data/beta-flags.json';
		$file_theme = get_template_directory() . '/beta-flags.json';
		// create a file in theme
		copy( $file_plugin, $file_theme );
		$flag_data = $this->admin->get_flag_data();
		$this->assertEquals( 'Fred Page', $flag_data->redesign_v109->author );
		// alter file in theme
		$json_text = file_get_contents( $file_theme );
		$json_text = str_replace( '"Fred Page"', '"Allison Page"', $json_text );
		file_put_contents( $file_theme, $json_text );
		$flag_data = $this->admin->get_flag_data();
		$this->assertEquals( 'Allison Page', $flag_data->redesign_v109->author );
		// alter file in theme to remove flags
		$json_text = str_replace( '"flags": {', '"flask": {', $json_text );
		file_put_contents( $file_theme, $json_text );
		$flag_data = $this->admin->get_flag_data();
		$this->assertEquals( 'No beta flag data found in configuration file', $flag_data );
		// alter file in theme to invalid JSON
		$json_text = str_replace( '"Allison Page"', '"Allison Page",', $json_text );
		file_put_contents( $file_theme, $json_text );
		$flag_data = $this->admin->get_flag_data();
		$this->assertEquals( 'Configuration file does not contain valid JSON', $flag_data );
		// delete file in theme
		unlink( $file_theme );
	}

	function tearDown() {
		parent::tearDown();
		unset( $GLOBALS['current_screen'] ); // get out of admin mode
	}

	private function make_user( $role ) {
		$user_id = $this->factory->user->create( array(
			'role' => $role,
		) );
		return $user_id;
	}

	private function make_settings() {
		$settings = new \stdClass;
		$settings->ab_test_on = 1;
		$settings->flags = array();
		$settings->flags['new_sidebar'] = array(
			'enabled' => 1,
			'ab_test' => 0,
		);
		$settings->flags['sticky_video'] = array(
			'enabled' => 0,
			'ab_test' => 0,
		);
		update_option( 'beta-flags', $settings );
	}

}
