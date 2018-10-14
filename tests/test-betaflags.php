<?php
/**
* Class BetaFlags
*
* @package BetaFlags
*/
namespace UnitTestBetaFlags;

class TestBetaFlags extends \WP_UnitTestCase {
	private $beta_flags;
	private $flag_args = [
		'key' => 'theme-show-sidebar',
		'title' => 'Show Sidebar',
		'description' => 'Add a sidebar to the post page',
		'author' => 'Charles',
		'ab_label' => 'absb',
		'enforced' => false,
	];

	function setUp() {
		parent::setUp();
		$this->beta_flags = new \BetaFlags\BetaFlags();
		$this->beta_flags->add_flag( $this->flag_args );
	}

	function test_add_flags() {
		$args = [
			'key' => 'plugin-test-v101',
			'title' => 'Show Plugin',
			'description' => 'Stuff is here',
			'author' => 'Jimmy Smith',
			'ab_label' => 'ab',
			'enforced' => false,
		];
		$this->beta_flags->add_flag( $args );
		$found = false;
		foreach ( $this->beta_flags->flags as $flag ) {
			if ( 'plugin-test-v101' === $flag->data['key'] ) {
				$found = true;
				break;
			}
		}
		$this->assertTrue( $found );
	}

	function test_add_flag_notice() {
		$this->beta_flags->admin_notice = 'This is an alert';
		ob_start();
		$this->beta_flags->add_flag_notice();
		$output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<div class="notice notice-error">', $output );
		$this->assertContains( 'This is an alert', $output );
	}

	function test_add_flag_validate_good() {
		$args = $this->flag_args;
		$args['key'] = 'second-key';
		$params = $this->beta_flags->add_flag_validate( $args );
		$this->assertEquals( $args, $params );
	}

	function test_add_flag_validate_duplicate_key() {
		$params = $this->beta_flags->add_flag_validate( $this->flag_args );
		$this->assertEquals( 'Key exists already', $params );
	}

	function test_add_flag_validate_key_characters() {
		$args = $this->flag_args;
		$args['key'] = 'junk#@!$#%@';
		$params = $this->beta_flags->add_flag_validate( $args );
		$this->assertEquals( 'Key can only contain lowercase letter, numbers, hyphens, and underscores', $params );
	}

	function test_add_flag_validate_ab_characters() {
		$args = $this->flag_args;
		$args['key'] = 'second-key';
		$args['ab_label'] = 'junk#@!$#%@';
		$params = $this->beta_flags->add_flag_validate( $args );
		$this->assertEquals( 'A/B Labels can only contain lowercase letter, numbers, hyphens, and underscores', $params );
	}

	function test_add_flag_validate_empty_title() {
		$args = $this->flag_args;
		$args['key'] = 'second-key';
		$args['title'] = '';
		$params = $this->beta_flags->add_flag_validate( $args );
		$this->assertEquals( 'No Name', $params['title'] );
	}

	function test_add_flag_validate_unset_key() {
		$args = $this->flag_args;
		unset( $args['key'] );
		$params = $this->beta_flags->add_flag_validate( $args );
		$this->assertEquals( 'You must supply a key', $params );
	}

	function test_add_flag_validate_bool_enforced() {
		$args = $this->flag_args;
		$args['key'] = 'second-key';
		$args['enforced'] = 'notboolean';
		$params = $this->beta_flags->add_flag_validate( $args );
		$this->assertEquals( false, $params['enforced'] );
	}

	function test_is_key_duplicate() {
		$output = $this->beta_flags->is_key_duplicate( $this->flag_args['key'] );
		$this->assertEquals( true, $output );
	}

	function test_find_flag() {
		$flag = $this->beta_flags->find_flag( $this->flag_args['key'] );
		$this->assertEquals( $flag->data['title'], $this->flag_args['title'] );
	}

	function test_get_flags_enabled() {
		$found = false;
		$flags = $this->beta_flags->get_flags( false );
		foreach ( $flags as $flag ) {
			if ( 'theme-show-sidebar' === $flag->data['key'] ) {
				$found = true;
			}
		}
		$this->assertTrue( $found );
	}

	function test_get_flags_enforced() {
		$args = [
			'key' => 'theme-show-sidebar-enforced',
			'title' => 'Show Sidebar Always',
			'description' => 'Add a sidebar to the post page',
			'author' => 'Charles',
			'ab_label' => 'absb',
			'enforced' => true,
		];
		$this->beta_flags->add_flag( $args );
		$found = false;
		$flags = $this->beta_flags->get_flags( true );
		foreach ( $flags as $flag ) {
			if ( 'theme-show-sidebar-enforced' === $flag->data['key'] ) {
				$found = true;
			}
		}
		$this->assertTrue( $found );
	}

	/**
	* Flag->is_active() is well tested already
	*/
	function test_is_active() {
		$state = $this->beta_flags->is_active( 'garbage' );
		$this->assertTrue( $state );
		$this->beta_flags->flags[0]->data['enabled'] = true;
		$state = $this->beta_flags->is_active( $this->flag_args['key'] );
		$this->assertTrue( $state );
	}

	function test_toggle_beta() {
		$state = $this->beta_flags->toggle_beta( $this->flag_args['key'] );
		$this->assertTrue( $state );
		$state = $this->beta_flags->toggle_beta( $this->flag_args['key'] );
		$this->assertFalse( $state );
	}

	function test_get_flag_settings() {
		$state = $this->beta_flags->toggle_beta( $this->flag_args['key'] );
		$settings = $this->beta_flags->get_flag_settings();
		$this->assertTrue( isset( $settings[ $this->flag_args['key'] ] ) );
	}

	function test_set_flag_settings() {
		$this->beta_flags->flag_settings = array( $this->flag_args['key']  => 1 );
		$this->beta_flags->set_flag_settings();
		$settings = get_option( FF_TEXT_DOMAIN, null );
		$this->assertTrue( isset( $settings[ $this->flag_args['key'] ] ) );
	}

	function tearDown() {
		parent::tearDown();
	}

}
