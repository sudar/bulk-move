<?php

namespace BulkWP\Tests\WPMock;

use WP_Mock\Tools\TestCase;

/**
 * Base WPMock Test case for Bulk WP Plugins.
 */
class BaseTestCase extends TestCase {

	/**
	 * List of files that are being tested.
	 *
	 * Only these files will be included during testing.
	 *
	 * @var array Test files.
	 */
	protected $test_files = [];

	public function setUp() {
		if ( ! empty( $this->test_files ) ) {
			foreach ( $this->test_files as $file ) {
				if ( file_exists( PLUGIN_ROOT . $file ) ) {
					require_once PLUGIN_ROOT . $file;
				}
			}
		}

		parent::setUp();
	}
}