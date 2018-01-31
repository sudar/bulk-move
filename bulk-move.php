<?php
/**
 * Plugin Name: Bulk Move
 * Plugin Script: bulk-move.php
 * Plugin URI: http://sudarmuthu.com/wordpress/bulk-move
 * Description: Move or remove posts in bulk from one category, tag or custom taxonomy to another
 * Version: 1.3.0
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Author: Sudar
 * Author URI: http://sudarmuthu.com/
 * Text Domain: bulk-move
 * Domain Path: languages/
 * === RELEASE NOTES ===
 * Checkout readme file for release notes.
 */

/**
 * Copyright 2009  Sudar Muthu  (email : sudar@sudarmuthu.com).
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA.
 */
defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Bulk Move autoloader.
 *
 * Ideally we should use psr-4 autoloading, but since this plugin supports PHP 5.2,
 * namespace are out of question. Hence this plugin uses PEAR coding standard.
 *
 * Eventually this will be upgraded to psr-4 standard once support for PHP 5.2 is dropped.
 *
 * @param string $class_name The name of the class that should be autoloaded.
 */
function bm_autoloader( $class_name ) {
	if ( false !== strpos( $class_name, 'BM' ) ) {
		$base_dir = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR;

		$class_path = str_replace( 'BM_', '', $class_name );
		$class_path = str_replace( '_', DIRECTORY_SEPARATOR, $class_path ) . '.php';

		$class_file = $base_dir . $class_path;

		if ( file_exists( $class_file ) ) {
			require_once $class_file;
		}
	}
}
spl_autoload_register( 'bm_autoloader' );

/**
 * Load Bulk Move plugin.
 *
 * @since 2.0.0
 */
function load_bulk_move() {
	$bulk_move = bulk_move();
	$bulk_move->set_plugin_file( __FILE__ );
	$bulk_move->add_loadie( new BM_Request_CustomTaxonomyAction() );

	add_action( 'plugins_loaded', array( $bulk_move, 'load' ), 101 );
}

load_bulk_move();

/**
 * The main function responsible for returning the one true BM_BulkMove
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: `$bulk_move = bulk_move();`
 *
 * @since  1.2.0
 *
 * @return \BM_BulkMove The one true BulkMove Instance.
 */
function bulk_move() {
	return BM_BulkMove::get_instance();
}
