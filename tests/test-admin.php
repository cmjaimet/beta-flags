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
		parent::setUp();
		$user_id = $this->make_user( 'administrator' );
		wp_set_current_user( $user_id );
		$this->admin = new \BetaFlags\FlagAdmin();
		pm_betaflag_register(
			[
				'key' => 'theme-show-sidebar',
				'title' => 'Sidebar in Testing',
				'description' => 'Add a sidebar to the post page',
				'author' => 'Charles',
				'ab_label' => 'ab',
				'enforced' => false,
			]
		);
		pm_betaflag_register(
			[
				'key' => 'theme-show-enforced',
				'title' => 'Lazy Load DFP Ads',
				'description' => 'Add JavaScript to allow bigbox (mid, bot) to lazy load on scroll',
				'author' => 'Steve Browning',
				'ab_label' => 'ab',
				'enforced' => true,
			]
		);
	}

	function test_admin_menu() {
		$this->admin->admin_menu();
		$this->assertNotEmpty( menu_page_url( FF_TEXT_DOMAIN, false ) );
	}

	function test_enqueue_styles() {
		$handle = 'beta-flags-styles';
		$screen_id = 'tools_page_beta-flags';
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
		set_current_screen( $screen_id );
		$this->admin->enqueue_scripts( $screen_id );
		$registered = wp_styles()->registered;
		$queue = wp_styles()->queue;
		$this->assertTrue( isset( $registered[ $handle ] ) );
		$this->assertTrue( in_array( $handle, $queue ) );
	}

	function test_enqueue_scripts() {
		$handle = 'beta-flags-scripts';
		$screen_id = 'tools_page_beta-flags';
		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
		set_current_screen( $screen_id );
		$this->admin->enqueue_scripts( $screen_id );
		$registered = wp_scripts()->registered;
		$queue = wp_scripts()->queue;
		$this->assertTrue( isset( $registered[ $handle ] ) );
		$this->assertTrue( in_array( $handle, $queue ) );
	}

	function test_settings_page() {
		ob_start();
		$this->admin->settings_page();
		$output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<div class="notice-container"></div>', $output );
		$this->assertContains( 'Available beta flags', $output );
		$this->assertContains( 'Enforced beta flags', $output );
		$nonce_value = wp_create_nonce( $this->admin->nonce_name );
		$this->assertContains( '<input type="hidden" id="' . $this->admin->nonce_name . '" value="' . $nonce_value . '" />', $output );
	}

	function test_list_flags_enabled() {
		ob_start();
		$this->admin->settings_page();
		$this->admin->list_flags(
			false,
			'Available beta flags',
			'Beta flags explainer.'
		);
		$output = ob_get_contents();
 		ob_end_clean();
		$this->assertContains( 'theme-show-sidebar', $output );
	}

	function test_list_flags_enforced() {
		ob_start();
		$this->admin->list_flags(
			true,
			'Enforced beta flags',
			'Betas listed etc.'
		);
		$output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( 'theme-show-enforced', $output );
	}

	function test_flag_validate_usercan() {
		$user_id = $this->make_user( 'editor' );
		wp_set_current_user( $user_id );
		$output = $this->admin->flag_validate();
		$this->assertContains( '(E451)', $output );
	}

	function test_flag_validate_nonce_missing() {
		$output = $this->admin->flag_validate();
		$this->assertContains( '(E353)', $output );
	}

	function test_flag_validate_nonce_wrong() {
		$_POST[ $this->admin->nonce_name ] = 'garbage';
		$output = $this->admin->flag_validate();
		$this->assertContains( '(E314)', $output );
	}

	function test_flag_validate_key_missing() {
		$nonce_value = wp_create_nonce( $this->admin->nonce_name );
		$_POST[ $this->admin->nonce_name ] = $nonce_value;
		$output = $this->admin->flag_validate();
		$this->assertContains( 'No beta key', $output );
	}

	function test_flag_validate_key_empty() {
		$nonce_value = wp_create_nonce( $this->admin->nonce_name );
		$_POST[ $this->admin->nonce_name ] = $nonce_value;
		$_POST['betaKey'] = ' ';
		$output = $this->admin->flag_validate();
		$this->assertContains( 'Beta key is blank', $output );
	}

	function test_flag_validate_success() {
		$nonce_value = wp_create_nonce( $this->admin->nonce_name );
		$_POST[ $this->admin->nonce_name ] = $nonce_value;
		$_POST['betaKey'] = 'plugin-stuff';
		$output = $this->admin->flag_validate();
		$this->assertEquals( '', $output );
	}

	function test_flag_enable_enabled() {
		$key = 'theme-show-sidebar';
		$_POST[ $this->admin->nonce_name ] = wp_create_nonce( $this->admin->nonce_name );
		$_POST['betaKey'] = $key;
		ob_start();
		$this->admin->flag_enable( true );
		$output = ob_get_contents();
		ob_end_clean();
		$data = json_decode( $output );
		$this->assertEquals( $data->key, $key );
		$this->assertEquals( $data->state, true );
	}

	function test_flag_enable_disabled() {
		$key = 'theme-show-sidebar';
		$this->flag->data['enabled'] = false;
		$_POST[ $this->admin->nonce_name ] = wp_create_nonce( $this->admin->nonce_name );
		$_POST['betaKey'] = $key;
		ob_start();
		$this->admin->flag_enable( true );
		$output = ob_get_contents();
		ob_end_clean();
		$data = json_decode( $output );
		$this->assertEquals( $data->key, $key );
		$this->assertEquals( $data->state, false );
	}

	function test_flag_enable_enforced() {
		$key = 'theme-show-sidebar';
		$this->flag->data['enforced'] = true;
		$_POST[ $this->admin->nonce_name ] = wp_create_nonce( $this->admin->nonce_name );
		$_POST['betaKey'] = $key;
		ob_start();
		$this->admin->flag_enable( true );
		$output = ob_get_contents();
		ob_end_clean();
		$data = json_decode( $output );
		$this->assertEquals( $data->key, $key );
		$this->assertEquals( $data->state, true );
	}

	function test_show_flag_icon_key() {
		$flag_key = 'theme-show-sidebar';
		ob_start();
		$this->admin->show_flag_icon( $flag_key, true, true );
		$output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( 'id="beta-flag-' . $flag_key . '"', $output );
	}

	function test_show_flag_icon_enforced() {
		$flag_key = 'theme-show-sidebar';
		ob_start();
		$this->admin->show_flag_icon( $flag_key, true, true );
		$output = ob_get_contents();
		ob_end_clean();
		$this->assertNotContains( 'status-marker-enabled', $output );
		$this->assertContains( 'status-marker-enforced', $output );
	}

	function test_show_flag_icon_enabled() {
		$flag_key = 'theme-show-sidebar';
		ob_start();
		$this->admin->show_flag_icon( $flag_key, false, true );
		$output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( 'status-marker-enabled', $output );
		$this->assertNotContains( 'status-marker-enforced', $output );
	}

	function test_show_flag_icon_disabled() {
		$flag_key = 'theme-show-sidebar';
		ob_start();
		$this->admin->show_flag_icon( $flag_key, false, false );
		$output = ob_get_contents();
		ob_end_clean();
		$this->assertNotContains( 'status-marker-enabled', $output );
		$this->assertNotContains( 'status-marker-enforced', $output );
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

}
