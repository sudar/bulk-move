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

if ( version_compare( PHP_VERSION, '5.6.20', '<' ) ) {
	/**
	 * Version 2.0.0 of the Bulk Move plugin increased the minimum required version of PHP to 5.6
	 * If you are still struck with PHP less than 5.6 and can't update, then use v1.3.0 of the plugin.
	 *
	 * @see   http://sudarmuthu.com/blog/why-i-am-dropping-support-for-php-5-2-in-my-wordpress-plugins/
	 * @see   https://wordpress.org/news/2019/04/minimum-php-version-update/
	 * @since 2.0.0
	 */
	function bulk_move_compatibility_notice() {
		?>
		<div class="error">
			<p>
				<?php
				printf(
					__( 'Bulk Move requires at least PHP 5.6.20 to function properly. Please upgrade PHP or use <a href="%s">v1.3.0 of Bulk Move</a>.', 'bulk-move' ), // @codingStandardsIgnoreLine
					'https://downloads.wordpress.org/plugin/bulk-move.1.3.0.zip'
				);
				?>
			</p>
		</div>
		<?php
	}
	add_action( 'admin_notices', 'bulk_move_compatibility_notice' );

	/**
	 * Deactivate Bulk Move.
	 *
	 * @since 2.0.0
	 */
	function bulk_move_deactivate() {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}
	add_action( 'admin_init', 'bulk_move_deactivate' );

	return;
}

// PHP is at least 5.6.20, so we can safely include namespace code.
require_once 'load-bulk-move.php';
bulk_move_load( __FILE__ );
