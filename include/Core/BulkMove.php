<?php
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
namespace BulkWP\BulkMove\Core;

use BulkWP\BulkMove\Core\Actions\LoadTaxonomyAction;
use BulkWP\BulkMove\Core\Actions\LoadTaxonomyTermAction;
use BulkWP\BulkMove\Core\Modules\Post\MoveCategoryModule;
use BulkWP\BulkMove\Core\Modules\Post\MoveCategoryToTagModule;
use BulkWP\BulkMove\Core\Modules\Post\MoveCustomTaxonomyModule;
use BulkWP\BulkMove\Core\Modules\Post\MoveTagModule;
use BulkWP\BulkMove\Core\Modules\Post\MoveTagToCategoryModule;
use BulkWP\BulkMove\Core\Modules\User\MoveRoleModule;
use BulkWP\BulkMove\Core\Pages\MovePostPage;
use BulkWP\BulkMove\Core\Pages\MoveUserPage;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Main Plugin class for Bulk Move.
 *
 * @since 2.0.0
 */
final class BulkMove {

	/**
	 * Plugin version.
	 */
	const VERSION = '2.0.0';

	/**
	 * Flag to track if the plugin is loaded.
	 *
	 * @var bool
	 */
	private $loaded = false;

	/**
	 * Path to main plugin file.
	 *
	 * @var string
	 */
	private $plugin_file = '';

	/**
	 * Path where translations are stored.
	 *
	 * @var string
	 */
	private $translations_path = '';

	/**
	 * Bulk Move Autoloader.
	 *
	 * Will be used by add-ons to extend the namespace.
	 *
	 * @var \BulkWP\BulkMove\BulkMoveAutoloader
	 */
	private $loader;

	/**
	 * Admin pages.
	 *
	 * @var array
	 */
	private $admin_pages = array();

	/**
	 * AJAX Handlers.
	 *
	 * @var array
	 */
	private $ajax_handlers = array();

	/**
	 * Singleton instance of the Plugin.
	 *
	 * @var BulkMove
	 */
	private static $instance = null;

	/**
	 * Conditionally creates the singleton instance if absent, else
	 * returns the previously saved instance.
	 *
	 * Insures that only one instance of BulkMove exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @static
	 *
	 * @return BulkMove The singleton instance
	 *
	 * @see \bulk_move()
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new BulkMove();
		}

		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since  1.2.0
	 * @access protected
	 *
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'bulk-move' ), self::VERSION );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @since  1.2.0
	 * @access protected
	 *
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'bulk-move' ), self::VERSION );
	}

	/**
	 * Load the plugin if it is not loaded.
	 *
	 * This function will be invoked in the `plugins_loaded` hook.
	 */
	public function load() {
		if ( $this->loaded ) {
			return;
		}

		add_action( 'init', array( $this, 'on_init' ) );
		add_action( 'admin_menu', array( $this, 'on_admin_menu' ) );

		add_filter( 'plugin_action_links', array( $this, 'filter_plugin_action_links' ), 10, 2 );

		$this->loaded = true;

		/**
		 * Bulk Move plugin loaded.
		 *
		 * @since 2.0.0
		 */
		do_action( 'bm_loaded' );
	}

	/**
	 * Triggered when the `init` hook is fired.
	 */
	public function on_init() {
		$this->load_textdomain();
		$this->load_ajax_handlers();
	}

	/**
	 * Load AJAX Handlers.
	 */
	private function load_ajax_handlers() {
		foreach ( $this->get_ajax_handlers() as $handler ) {
			$handler->load();
		}
	}

	/**
	 * Get the list of AJAX Handlers.
	 *
	 * @return array List of AJAX Handlers.
	 */
	private function get_ajax_handlers() {
		if ( empty( $this->ajax_handlers ) ) {
			$this->ajax_handlers[] = new LoadTaxonomyAction();
			$this->ajax_handlers[] = new LoadTaxonomyTermAction();
		}

		/**
		 * List of ajax handlers.
		 *
		 * @since 2.0.0
		 *
		 * @param array List of AJAX Handlers.
		 */
		return apply_filters( 'bm_ajax_handlers', $this->ajax_handlers );
	}

	/**
	 * Triggered when the `admin_menu` hook is fired.
	 *
	 * Register all admin pages.
	 */
	public function on_admin_menu() {
		foreach ( $this->get_admin_pages() as $page ) {
			$page->register();
		}
	}

	/**
	 * Get the list of registered admin pages.
	 *
	 * @return \BulkWP\BulkMove\Core\Pages\BaseMovePage[] List of Admin pages.
	 */
	private function get_admin_pages() {
		if ( empty( $this->admin_pages ) ) {
			$posts_page                                   = $this->get_post_admin_page();
			$this->admin_pages[ $posts_page->get_slug() ] = $posts_page;

			$users_page                                   = $this->get_user_admin_page();
			$this->admin_pages[ $users_page->get_slug() ] = $users_page;
		}

		/**
		 * List of admin pages.
		 *
		 * @since 2.0.0
		 *
		 * @param \BulkWP\BulkMove\Core\Pages\BaseMovePage[] List of Admin pages.
		 */
		return apply_filters( 'bm_admin_pages', $this->admin_pages );
	}

	/**
	 * Loads the plugin language files.
	 *
	 * @since  1.2.0
	 */
	private function load_textdomain() {
		load_plugin_textdomain( 'bulk-move', false, $this->get_translations_path() );
	}

	/**
	 * Adds the settings link in the Plugin page.
	 *
	 * Based on http://striderweb.com/nerdaphernalia/2008/06/wp-use-action-links/.
	 *
	 * @staticvar string $this_plugin
	 *
	 * @param array  $action_links Action Links.
	 * @param string $file         Plugin file name.
	 *
	 * @return array Modified links.
	 */
	public function filter_plugin_action_links( $action_links, $file ) {
		static $this_plugin;

		if ( ! $this_plugin ) {
			$this_plugin = plugin_basename( $this->get_plugin_file() );
		}

		if ( $file == $this_plugin ) {
			/**
			 * Filter plugin action links added by Bulk Move.
			 *
			 * @since 2.0.0
			 *
			 * @param array Plugin Links.
			 */
			$bm_action_links = apply_filters( 'bm_plugin_action_links', array() );

			if ( ! empty( $bm_action_links ) ) {
				$action_links = array_merge( $bm_action_links, $action_links );
			}
		}

		return $action_links;
	}

	/**
	 * Get path to main plugin file.
	 *
	 * @return string Plugin file.
	 */
	public function get_plugin_file() {
		return $this->plugin_file;
	}

	/**
	 * Set path to main plugin file.
	 *
	 * @param string $plugin_file Path to main plugin file.
	 */
	public function set_plugin_file( $plugin_file ) {
		$this->plugin_file       = $plugin_file;
		$this->translations_path = dirname( plugin_basename( $this->get_plugin_file() ) ) . '/languages/';
	}

	/**
	 * Setter for Autoloader.
	 *
	 * @param \BulkWP\BulkMove\BulkMoveAutoloader $loader Autoloader.
	 */
	public function set_loader( $loader ) {
		$this->loader = $loader;
	}

	/**
	 * Get path to translations.
	 *
	 * @return string Translations path.
	 */
	public function get_translations_path() {
		return $this->translations_path;
	}

	/**
	 * Get Bulk Move Post admin page.
	 *
	 * @return \BulkWP\BulkMove\Core\Pages\MovePostPage Bulk Move Post admin page.
	 */
	private function get_post_admin_page() {
		$posts_page = new MovePostPage();

		$posts_page->add_module( new MoveCategoryModule() );
		$posts_page->add_module( new MoveTagModule() );
		$posts_page->add_module( new MoveCustomTaxonomyModule() );
		$posts_page->add_module( new MoveTagToCategoryModule() );
		$posts_page->add_module( new MoveCategoryToTagModule() );

		/**
		 * Triggered after the modules are added to a page.
		 *
		 * @since 2.0.0
		 *
		 * @param \BulkWP\BulkMove\Core\Pages\BaseMovePage The page for which modules are added.
		 */
		do_action( 'bm_after_modules', $posts_page );
		do_action( 'bm_after_modules_' . $posts_page->get_slug(), $posts_page );

		return $posts_page;
	}

	/**
	 * Gets the Bulk Move User admin page.
	 *
	 * @return \BulkWP\BulkMove\Core\Pages\MoveUserPage Bulk Move User admin page.
	 */
	private function get_user_admin_page() {
		$users_page = new MoveUserPage();

		$users_page->add_module( new MoveRoleModule() );

		/**
		 * Triggered after the modules are added to a page.
		 *
		 * @since 2.0.0
		 *
		 * @param \BulkWP\BulkMove\Core\Pages\BaseMovePage The page for which modules are added.
		 */
		do_action( 'bm_after_modules', $users_page );
		do_action( 'bm_after_modules_' . $users_page->get_slug(), $users_page );

		return $users_page;
	}
}
