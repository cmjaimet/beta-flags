<?php
/**
* Class BetaFlags
*
* @package BetaFlags
*/
namespace UnitTestBetaFlags;

class TestBetaFlags extends \WP_UnitTestCase {
	private $beta_flags;

	function setUp() {
		parent::setUp();
		$this->make_settings();
		$this->beta_flags = new \BetaFlags\BetaFlags();
	}

	function test_is_enabled() {
		$this->assertFalse( $this->beta_flags->is_enabled( 'doesnt_exist' ) );
		$this->assertFalse( $this->beta_flags->is_enabled( 'bad_data' ) );
		$this->assertTrue( $this->beta_flags->is_enabled( 'new_sidebar' ) );
		$this->assertFalse( $this->beta_flags->is_enabled( 'sticky_video' ) );
		$this->beta_flags->flag_settings->ab_test_on = 0;
		$this->assertTrue( $this->beta_flags->is_enabled( 'fullsized_ads' ) );
	}

	function test_is_ab_active() {
		$this->beta_flags->flag_settings->ab_test_on = 0;
		$this->assertTrue( $this->beta_flags->is_ab_active( 0 ) );
		$this->assertTrue( $this->beta_flags->is_ab_active( 1 ) );
		$this->beta_flags->flag_settings->ab_test_on = 1;
		$this->assertTrue( $this->beta_flags->is_ab_active( 0 ) );
		$this->assertTrue( $this->beta_flags->is_ab_active( 1 ) );
		set_query_var( 'ab', '1' );
		$this->assertFalse( $this->beta_flags->is_ab_active( 1 ) );
	}

	function test_abtest_post_link() {
		$url_base = 'http://www.domain.com/hello-world';
		// qs addition is random (50%) so let it run a few times then break when it works
		for ( $x = 0; $x < 10; $x ++ ) {
			$url = $this->beta_flags->abtest_post_link( $url_base, null, null );
			if ( '?ab=1' === substr( $url, -5 ) ) {
				$this->assertTrue( true );
				break;
			}
		}
	}

	function test_abtest_term_link() {
		$url_base = 'http://www.domain.com/hello-world';
		// qs addition is random (50%) so let it run a few times then break when it works
		for ( $x = 0; $x < 10; $x ++ ) {
			$url = $this->beta_flags->abtest_term_link( $url_base, null, null );
			if ( '?ab=1' === substr( $url, -5 ) ) {
				$this->assertTrue( true );
				break;
			}
		}
	}

	function test_abtest_query_string() {
		$url_base = 'http://www.domain.com/hello-world';
		// qs addition is random (50%) so let it run a few times then break when it works
		for ( $x = 0; $x < 10; $x ++ ) {
			$url = $this->beta_flags->abtest_query_string( $url_base );
			if ( '?ab=1' === substr( $url, -5 ) ) {
				$this->assertTrue( true );
				break;
			}
		}
	}

	function test_query_vars_filter() {
		$vars = array( 'dog', 'cat' );
		$vars = $this->beta_flags->query_vars_filter( $vars );
		$this->assertContains( 'ab', $vars );
	}

	function tearDown() {
		parent::tearDown();
	}

	private function make_settings() {
		$settings = new \stdClass;
		$settings->ab_test_on = 1;
		$settings->flags = array();
		$settings->flags['fullsized_ads'] = array(
			'enabled' => 1,
			'ab_test' => 1,
		);
		$settings->flags['new_sidebar'] = array(
			'enabled' => 1,
			'ab_test' => 0,
		);
		$settings->flags['sticky_video'] = array(
			'enabled' => 0,
			'ab_test' => 1,
		);
		$settings->flags['redesign_v109'] = array(
			'enabled' => 0,
			'ab_test' => 0,
		);
		$settings->flags['bad_data'] = array();
		update_option( FF_TEXT_DOMAIN, $settings );
	}

}
