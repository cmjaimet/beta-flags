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
	}

	function xtest_list_flags() {
	}

	function xtest_get_flag_setting() {
	}

	function xtest_form_submit() {
	}

	function xtest_form_validate() {
	}

	function xtest_get_flag_data() {
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
