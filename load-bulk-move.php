<?php
/**
 * Load Bulk Move plugin.
 *
 * We need this load code in a separate file since it requires namespace
 * and using namespace in PHP 5.2 will generate a fatal error.
 *
 * @since 2.0.0
 */
use BulkWP\BulkMove\BulkMoveAutoloader;
use BulkWP\BulkMove\Core\BulkMove;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Load Bulk Move plugin.
 *
 * @since 2.0.0
 *
 * @param string $plugin_file Main plugin file.
 */
function bulk_move_load( $plugin_file ) {
	$plugin_dir = plugin_dir_path( $plugin_file );

	// setup autoloader.
	require_once 'include/BulkMoveAutoloader.php';

	$loader = new BulkMoveAutoloader();
	$loader->add_namespace( 'BulkWP\\BulkMove\\', $plugin_dir . 'include' );
	$loader->register();

	$plugin = BulkMove::get_instance();
	$plugin->set_plugin_file( $plugin_file );
	$plugin->set_loader( $loader );

	add_action( 'plugins_loaded', array( $plugin, 'load' ), 101 );
}

/**
 * The main function responsible for returning the one true BulkMove
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: `$bulk_move = bulk_move();`
 *
 * @since  1.2.0
 *
 * @return BulkMove The one true BulkMove Instance.
 */
function bulk_move() {
	return BulkMove::get_instance();
}
