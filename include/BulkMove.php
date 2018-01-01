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
defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Main Plugin class for Bulk Move.
 *
 * @since 2.0.0
 */
final class BM_BulkMove {

	/**
	 * Plugin version.
	 */
	const VERSION = '1.3.0';

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
	 * Admin pages.
	 *
	 * @var array
	 */
	private $admin_pages = array();

	/**
	 * Singleton instance of the Plugin.
	 *
	 * @var \BM_BulkMove
	 */
	private static $instance = null;

	/**
	 * Conditionally creates the singleton instance if absent, else
	 * returns the previously saved instance.
	 *
	 * Insures that only one instance of BM_BulkMove exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @static
	 *
	 * @see \bulk_move()
	 *
	 * @return \BM_BulkMove The singleton instance
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new BM_BulkMove();
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
	}

	/**
	 * Triggered when the `init` hook is fired.
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
	 * @return \BM_Page_Base[] List of Admin pages.
	 */
	private function get_admin_pages() {
		if ( empty( $this->admin_pages ) ) {
			$posts_page                                   = $this->get_post_admin_page();
			$this->admin_pages[ $posts_page->get_slug() ] = $posts_page;
		}

		/**
		 * List of admin pages.
		 *
		 * @since 2.0.0
		 *
		 * @param \BM_Page_Base[] List of Admin pages.
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
	 * @return \BM_Page_Post Bulk Move Post admin page.
	 */
	private function get_post_admin_page() {
		$posts_page = new BM_Page_Post();

		// TODO: Add other metaboxes.
		$posts_page->add_metabox( new BM_Metabox_Posts_Category() );
		$posts_page->add_metabox( new BM_Metabox_Posts_Tag() );
        $posts_page->add_metabox( new BM_Metabox_Posts_Tagtocategory() );

		return $posts_page;
	}
}
