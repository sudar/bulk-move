<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Bulk_Move
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	throw new Exception( "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/../bulk-move.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

// Load BulkWP test tools.
if ( ! file_exists( dirname( dirname( __FILE__ ) ) . '/../vendor/sudar/wp-plugin-test-tools/src/Tests/WPCore/WPCoreUnitTestCase.php' ) ) {
	echo 'Could not find BulkWP Test tools. Have you run composer install?' . PHP_EOL;
	exit( 1 );
}
require_once dirname( dirname( __FILE__ ) ) . '/../vendor/sudar/wp-plugin-test-tools/src/Tests/WPCore/WPCoreUnitTestCase.php';
