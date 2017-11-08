<?php
/**
 * Plugin Name: Bulk Move
 * Plugin Script: bulk-move.php
 * Plugin URI: http://sudarmuthu.com/wordpress/bulk-move
 * Description: Move or remove posts in bulk from one category, tag or custom taxonomy to another
 * Version: 1.3.0
 * License: GPL
 * Author: Sudar
 * Author URI: http://sudarmuthu.com/
 * Text Domain: bulk-move
 * Domain Path: languages/.
 *
 * === RELEASE NOTES ===
 * Checkout readme file for release notes
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
 * Main Plugin class for Bulk Move.
 *
 * Singleton @since 1.2.0
 */
final class Bulk_Move {
	/**
	 * @var Bulk_Move The one true Bulk_Move
	 *
	 * @since 1.2.0
	 */
	private static $instance;

	// version
	const VERSION = '1.3.0';

	// page slugs
	const POSTS_PAGE_SLUG = 'bulk-move-posts';

	// JS constants
	const JS_HANDLE   = 'bulk-move';
	const JS_VARIABLE = 'BULK_MOVE';

	// CSS constants
	const CSS_HANDLE = 'bulk-move';

	// meta boxes for move posts
	const BOX_CATEGORY        = 'bm_move_category';
	const BOX_CATEGORY_BY_TAG = 'bm_move_category_by_tag';
	const BOX_TAG             = 'bm_move_tag';
	const BOX_TERMS           = 'bm_move_terms';
	const BOX_DEBUG           = 'bm_debug';

	const BOX_CUSTOM_TERMS_NONCE = 'smbm-bulk-move-by-custom-terms';

	// options
	const SCRIPT_TIMEOUT_OPTION = 'bm_max_execution_time';

	public $translations;
	public $post_page;
	public $move_posts_screen;
	public $msg;

	// path variables
	// Ideally these should be constants, but because of PHP's limitations, these are static varaibles
	public static $PLUGIN_DIR;
	public static $PLUGIN_URL;
	public static $PLUGIN_FILE;

	/**
	 * Main Bulk_Move Instance.
	 *
	 * Insures that only one instance of Bulk_Move exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.2.0
	 * @static
	 * @staticvar array $instance
	 *
	 * @uses Bulk_Move::setup_paths() Setup the plugin paths
	 * @uses Bulk_Move::includes() Include the required files
	 * @uses Bulk_Move::load_textdomain() Load text domain for translation
	 * @uses Bulk_Move::setup_actions() Setup the hooks and actions
	 *
	 * @see BULK_MOVE()
	 *
	 * @return \Bulk_Move The one true BULK_MOVE
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Bulk_Move ) ) {
			self::$instance = new Bulk_Move();
			self::$instance->setup_paths();
			self::$instance->includes();
			self::$instance->load_textdomain();
			self::$instance->setup_actions();
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
	 * Setup plugin constants.
	 *
	 * @access private
	 *
	 * @since  1.2.0
	 *
	 * @return void
	 */
	private function setup_paths() {
		// Plugin Folder Path
		self::$PLUGIN_DIR = plugin_dir_path( __FILE__ );

		// Plugin Folder URL
		self::$PLUGIN_URL = plugin_dir_url( __FILE__ );

		// Plugin Root File
		self::$PLUGIN_FILE = __FILE__;
	}

	/**
	 * Include required files.
	 *
	 * @access private
	 *
	 * @since  1.2.0
	 *
	 * @return void
	 */
	private function includes() {
		require_once self::$PLUGIN_DIR . '/include/class-bulk-move-posts.php';
		require_once self::$PLUGIN_DIR . '/include/class-bulk-move-util.php';
	}

	/**
	 * Loads the plugin language files.
	 *
	 * @since  1.2.0
	 */
	public function load_textdomain() {
		$this->translations = dirname( plugin_basename( self::$PLUGIN_FILE ) ) . '/languages/';
		load_plugin_textdomain( 'bulk-move', false, $this->translations );
	}

	/**
	 * Loads the plugin's actions and hooks.
	 *
	 * @access private
	 *
	 * @since  1.2.0
	 *
	 * @return void
	 */
	private function setup_actions() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'request_handler' ) );

		add_filter( 'plugin_action_links', array( $this, 'filter_plugin_actions' ), 10, 2 );
	}

	/**
	 * Add navigation menu.
	 */
	public function add_menu() {

		$this->post_page = add_submenu_page( 'tools.php', __( 'Bulk Move' , 'bulk-move' ), __( 'Bulk Move' , 'bulk-move' ), 'edit_others_posts', self::POSTS_PAGE_SLUG, array( $this, 'display_posts_page' ) );

		add_action( 'admin_print_scripts-' . $this->post_page, array( $this, 'add_script' ) );
		add_action( 'admin_print_scripts-' . $this->post_page, array( $this, 'add_styles' ) );

		add_action( "load-{$this->post_page}", array( $this, 'add_move_posts_settings_panel' ) );
		add_action( "add_meta_boxes_{$this->post_page}", array( $this, 'add_move_posts_meta_boxes' ) );
	}

	/**
	 * Add settings Panel for move posts page.
	 *
	 * @since 1.0
	 */
	public function add_move_posts_settings_panel() {

		/**
		 * Create the WP_Screen object using page handle.
		 */
		$this->move_posts_screen = WP_Screen::get( $this->post_page );

		/**
		 * Content specified inline.
		 */
		$this->move_posts_screen->add_help_tab(
			array(
				'title'    => __( 'About Plugin', 'bulk-move' ),
				'id'       => 'about_tab',
				'content'  => '<p>' . __( 'This plugin allows you to move posts in bulk from selected categories to another category', 'bulk-move' ) . '</p>',
				'callback' => false,
			)
		);

		$this->move_posts_screen->set_help_sidebar(
			'<p><strong>' . __( 'More information', 'bulk-move' ) . '</strong></p>' .
			'<p><a href = "http://sudarmuthu.com/wordpress/bulk-move">' . __( 'Plugin Homepage/support', 'bulk-move' ) . '</a></p>' .
			'<p><a href = "http://sudarmuthu.com/blog">' . __( "Plugin author's blog", 'bulk-move' ) . '</a></p>' .
			'<p><a href = "http://sudarmuthu.com/wordpress/">' . __( "Other Plugin's by Author", 'bulk-move' ) . '</a></p>'
		);

		/* Trigger the add_meta_boxes hooks to allow meta boxes to be added */
		do_action( 'add_meta_boxes_' . $this->post_page, null );

		/* Enqueue WordPress' script for handling the meta boxes */
		wp_enqueue_script( 'postbox' );
	}

	/**
	 * Register meta boxes for move posts page.
	 *
	 * @since 1.0
	 */
	public function add_move_posts_meta_boxes() {
		add_meta_box( self::BOX_CATEGORY, __( 'Bulk Move By Category', 'bulk-move' ), 'Bulk_Move_Posts::render_move_category_box', $this->post_page, 'advanced' );
		add_meta_box( self::BOX_TAG, __( 'Bulk Move By Tag', 'bulk-move' ), 'Bulk_Move_Posts::render_move_tag_box', $this->post_page, 'advanced' );
		add_meta_box( self::BOX_CATEGORY_BY_TAG, __( 'Bulk Move Category By Tag', 'bulk-move' ), 'Bulk_Move_Posts::render_move_category_by_tag_box', $this->post_page, 'advanced' );
		add_meta_box( self::BOX_TERMS, __( 'Bulk Move By Custom Taxonomy', 'bulk-move' ), 'Bulk_Move_Posts::render_move_by_custom_taxonomy_box', $this->post_page, 'advanced' );
		add_meta_box( self::BOX_DEBUG, __( 'Debug Information', 'bulk-move' ), 'Bulk_Move_Posts::render_debug_box', $this->post_page, 'advanced', 'low' );
	}

	/**
	 * Show the Admin page.
	 */
	public function display_posts_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'Bulk Move Posts', 'bulk-move' ); ?></h2>

			<form method = "post">
				<?php
				wp_nonce_field( 'sm-bulk-move-posts', 'sm-bulk-move-posts-nonce' );

				/* Used to save closed meta boxes and their order */
				wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
				wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
				?>
				<div id = "poststuff">
					<div id="post-body" class="metabox-holder columns-1">

						<div id="post-body-content">
							<div class="updated" >
								<p><strong><?php _e( 'WARNING: Posts moved once cannot be retrieved back. Use with caution.', 'bulk-move' ); ?></strong></p>
							</div>
						</div><!-- #post-body-content -->

						<div id="postbox-container-1" class="postbox-container">
							<?php do_meta_boxes( '', 'advanced', null ); ?>
						</div> <!-- #postbox-container-2 -->

					</div> <!-- #post-body -->
				</div><!-- #poststuff -->

			</form>
		</div><!-- .wrap -->

		<?php
		add_action( 'in_admin_footer', array( $this, 'display_credits_in_footer' ) );
	}

	/**
	 * Adds Footer links.
	 *
	 * Based on http://striderweb.com/nerdaphernalia/2008/06/give-your-wordpress-plugin-credit/.
	 */
	public function display_credits_in_footer() {
		$plugin_data = get_plugin_data( __FILE__ );
		printf( '%1$s ' . __( 'plugin', 'bulk-move' ) . ' | ' . __( 'Version', 'bulk-move' ) . ' %2$s | ' . __( 'by', 'bulk-move' ) . ' %3$s<br />', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author'] );
	}

	/**
	 * Enqueue JavaScript.
	 */
	public function add_script() {
		wp_enqueue_script( self::JS_HANDLE, plugins_url( '/assets/js/bulk-move.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );

		$msg = array(
			'move_warning' => __( 'Are you sure you want to move all the selected posts', 'bulk-move' ),
		);

		$error = array(
			'select_one' => __( 'Please select least one option', 'bulk-move' ),
		);

		// TODO: Add this from Bulk_Move_Posts class using a filter.
		$bulk_move_posts = array(
			'action_get_taxonomy' => 'load_custom_taxonomy_by_post_type',
			'action_get_terms'    => 'load_custom_terms_by_taxonomy',
			'nonce'               => wp_create_nonce( self::BOX_CUSTOM_TERMS_NONCE ),
		);

		$translation_array = array(
			'msg'             => $msg,
			'error'           => $error,
			'bulk_move_posts' => $bulk_move_posts,
		);
		wp_localize_script( self::JS_HANDLE, self::JS_VARIABLE, $translation_array );
	}

	/**
	 * Enqueue styles.
	 *
	 * @since 1.2.0
	 */
	public function add_styles() {
		wp_enqueue_style( self::CSS_HANDLE, plugins_url( '/assets/css/bulk-move.css', __FILE__ ), false, self::VERSION );
	}

	/**
	 * Request Handler.
	 */
	public function request_handler() {
		if ( isset( $_POST['bm_action'] ) ) {
			do_action( 'bm_' . $_POST['bm_action'], $_POST );
		}

		add_action( 'admin_notices', array( $this, 'moved_notice' ), 9 );
	}

	/**
	 * Show moved notice messages.
	 */
	public function moved_notice() {
		if ( isset( $this->msg ) && $this->msg != '' ) {
			echo "<div class = 'updated'><p>" . $this->msg . '</p></div>';
		}

		// cleanup.
		$this->msg = '';
		remove_action( 'admin_notices', array( $this, 'moved_notice' ) );
	}

	/**
	 * Adds the settings link in the Plugin page.
	 *
	 * Based on http://striderweb.com/nerdaphernalia/2008/06/wp-use-action-links/.
	 *
	 * @staticvar string $this_plugin
	 *
	 * @param array  $links Links.
	 * @param string $file  Plugin file name.
	 *
	 * @return array Modified links.
	 */
	public function filter_plugin_actions( $links, $file ) {
		static $this_plugin;

		if ( ! $this_plugin ) {
			$this_plugin = plugin_basename( __FILE__ );
		}

		if ( $file == $this_plugin ) {
			$settings_link = '<a href="tools.php?page=' . self::POSTS_PAGE_SLUG . '">' . __( 'Manage', 'bulk-move' ) . '</a>';
			array_unshift( $links, $settings_link ); // before other links.
		}

		return $links;
	}
}

/**
 * The main function responsible for returning the one true Bulk_Move
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: `$bulk_move = BULK_MOVE();`
 *
 * @since  1.2.0
 *
 * @return \Bulk_Move The one true Bulk_Move Instance
 */
function BULK_MOVE() {
	return Bulk_Move::instance();
}

// Get BULK_MOVE Running.
BULK_MOVE();
