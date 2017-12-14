<?php
/**
 * PHPUnit bootstrap file for WP Mock
 *
 * @package Bulk_Move
 */

// First we need to load the composer autoloader so we can use WP Mock.
require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/includes/wp-function-mocks.php';

// Now call the bootstrap method of WP Mock.
WP_Mock::bootstrap();

/**
 * Now we include any plugin files that we need to be able to run the tests. This
 * should be files that define the functions and classes you're going to test.
 */
require dirname( dirname( __FILE__ ) ) . '/../bulk-move.php';
