<?php
/**
* Class BetaFlags
*
* @package BetaFlags
*/
namespace UnitTestBetaFlags;

class TestFlag extends \WP_UnitTestCase {
	private $flag;

	function setUp() {
		parent::setUp();
		$args =  [
			'key' => 'theme-show-sidebar',
			'title' => 'Show Sidebar',
			'description' => 'Add a sidebar to the post page',
			'author' => 'Charles',
			'ab_label' => 'ab',
			'enforced' => false,
		];
		$this->flag = new \BetaFlags\Flag( $args );
	}

	function test_get_key() {
		$this->assertEquals( $this->flag->get( 'key' ), 'theme-show-sidebar' );
		$this->assertEquals( $this->flag->get( 'title' ), 'Show Sidebar' );
		$this->assertEquals( $this->flag->get( 'description' ), 'Add a sidebar to the post page' );
		$this->assertEquals( $this->flag->get( 'author' ), 'Charles' );
		$this->assertEquals( $this->flag->get( 'ab_label' ), 'ab' );
		$this->assertEquals( $this->flag->get( 'enforced' ), false );
	}

	function test_is_active_enforced() {
		$this->flag->data['enforced'] = true;
		$this->assertTrue( $this->flag->is_active() );
	}

	function test_is_active_disabled() {
		$this->flag->data['enabled'] = false;
		$this->assertFalse( $this->flag->is_active() );
	}

	function test_is_active_admin() {
		$this->flag->data['enabled'] = true;
		$this->flag->data['enforced'] = false;
		set_current_screen( 'tools_page_feature-flags' );
		$this->assertTrue( is_admin() );
		$this->assertTrue( $this->flag->is_active() );
	}

	function test_is_active_abtest() {
		$this->assertFalse( is_admin() );
		$this->flag->data['enabled'] = true;
		$this->assertTrue( $this->flag->is_active() );
	}

	function test_is_active_abtest_on() {
		$this->flag->data['enabled'] = true;
		$this->go_to( '/?ab=yes' );
		$this->assertFalse( is_admin() );
		$this->assertFalse( $this->flag->is_active() );
	}

	function test_is_ab_active_value() {
		$this->flag->data['enabled'] = true;
		$this->go_to( '/?ab=yes' );
		$this->assertFalse( is_admin() );
		$this->assertFalse( $this->flag->is_ab_active() );
	}

	function test_is_ab_active_empty() {
		$this->flag->data['enabled'] = true;
		$this->go_to( '/?ab=' );
		$this->assertFalse( is_admin() );
		$this->assertFalse( $this->flag->is_ab_active() );
	}

	function test_is_ab_active_absent() {
		$this->flag->data['enabled'] = true;
		$this->go_to( '/' );
		$this->assertFalse( is_admin() );
		$this->assertTrue( $this->flag->is_ab_active() );
	}

	function tearDown() {
		parent::tearDown();
		unset( $GLOBALS['current_screen'] ); // get out of admin mode
	}

}
