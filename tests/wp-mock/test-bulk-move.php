<?php
/**
 * Class SampleTest
 *
 * @package Bulk_Move
 */

/**
 * Sample test case.
 */
class BulkMoveTest extends WP_Mock\Tools\TestCase {

	public function setUp() {
		\WP_Mock::setUp();
	}

	public function tearDown() {
		\WP_Mock::tearDown();
	}

	function test_translation_is_loaded() {
		\WP_Mock::userFunction( 'load_plugin_textdomain', array(
			'times' => 1,
			'args' => array( 'bulk-move', false, \WP_Mock\Functions::type( 'string' ) )
		) );

//		\WP_Mock::expectAction( 'admin_menu' );
//		\WP_Mock::expectAction( 'admin_init' );

		BULK_MOVE();

		$this->assertConditionsMet();
	}
}
