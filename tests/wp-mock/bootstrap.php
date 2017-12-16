<?php
/**
 * PHPUnit bootstrap file for WP Mock
 *
 * @package Bulk_Move
 */

// Plugin root.
if ( ! defined( 'PLUGIN_ROOT' ) ) {
	define( 'PLUGIN_ROOT', __DIR__ . '/../../' );
}

// First we need to load the composer autoloader so we can use WP Mock.
require_once __DIR__ . '/../../vendor/autoload.php';

// Mocks for WordPress core functions.
require_once __DIR__ . '/includes/BaseTestCase.php';

// Now call the bootstrap method of WP Mock.
WP_Mock::setUsePatchwork( true );
WP_Mock::bootstrap();

// Mocks for WordPress core functions.
require_once __DIR__ . '/includes/wp-function-mocks.php';
