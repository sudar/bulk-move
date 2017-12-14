<?php

namespace BM;

use WP_Mock\Tools\TestCase;

/**
 * Test BM_BulkMove.
 *
 * TODO: Add tests for autoloader
 * TODO: Add tests for plugin_file
 * TODO: Add tests for setting translations path
 */
class BulkMoveTest extends TestCase {

	public function setUp() {
		\WP_Mock::setUp();
	}

	public function tearDown() {
		\WP_Mock::tearDown();
	}

	function test_it_is_singleton() {
		$a = \BM_BulkMove::get_instance();
		$b = \BM_BulkMove::get_instance();

		$this->assertSame( $a, $b );
	}

	function test_load_action() {
		\WP_Mock::expectAction( 'bm_loaded' );

		$bulk_move = bulk_move();
		$bulk_move->load();

		$this->assertConditionsMet();
	}

	function test_translation_is_loaded() {
		\WP_Mock::userFunction( 'load_plugin_textdomain', array(
			'times' => 1,
			'args' => array( 'bulk-move', false, \WP_Mock\Functions::type( 'string' ) )
		) );

		$bulk_move = bulk_move();
		$bulk_move->on_init();

		$this->assertConditionsMet();
	}
}
