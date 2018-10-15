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
		$this->assertNotEmpty( menu_page_url( FF_TEXT_DOMAIN, false ) );
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
		$this->assertTrue( true ); // placeholder to prevent PHPUnit warnings until test written
	}

	function test_form_validate() {
		$this->assertEquals( $this->admin->form_validate(), 'You are not authorized to perform that action (E353)' );
		$_POST[ 'betaflagsnonce' ] = 'baddata';
		$this->assertEquals( $this->admin->form_validate(), 'You are not authorized to perform that action (E314)' );
		$nonce_value = wp_create_nonce( 'betaflagsnonce' );
		$_POST[ 'betaflagsnonce' ] = $nonce_value;
		$this->assertEquals( $this->admin->form_validate(), '' );
		$user_id = $this->make_user( 'author' );
		wp_set_current_user( $user_id );
		$this->assertEquals( $this->admin->form_validate(), 'You are not authorized to perform that action (E451)' );
	}

	function test_get_flag_data() {
		$flag_data = $this->admin->get_flag_data();
		$this->assertEquals( 'Fred Page', $flag_data->redesign_v109->author );
		// $json_plugin = FF_PLUGIN_PATH . 'data/beta-flags.json';
		// $json_theme = get_template_directory() . '/beta-flags.json';
		// create a file in theme
		// copy( $json_plugin, $json_theme );
		// $flag_data = $this->admin->get_flag_data();
		// $this->assertEquals( 'Fred Page', $flag_data->redesign_v109->author );
		// $json_text = file_get_contents( $json_theme );
		// $json_text = str_replace( '"Fred Page"', '"Allison Page"', $json_text );
		// file_put_contents( $json_theme, $json_text );
		// $flag_data = $this->admin->get_flag_data();
		// $this->assertEquals( 'Allison Page', $flag_data->redesign_v109->author );
		// alter file in theme
		// $json_text = str_replace( '"Allison Page"', '"Allison Page",', $json_text );
		// file_put_contents( $json_theme, $json_text );
		// $flag_data = $this->admin->get_flag_data();
		// $this->assertEquals( 'Configuration file does not contain valid JSON', $flag_data );
		// delete file in theme
		// alter file in plugin
		// delete file in plugin
		// delete file in plugin
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
			'ab_test' => 0
		);
		$settings->flags['sticky_video'] = array(
			'enabled' => 0,
			'ab_test' => 0
		);
		update_option( FF_TEXT_DOMAIN, $settings );
	}

}
