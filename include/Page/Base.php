<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * A Bulk Move admin page.
 *
 * @since 2.0.0
 */
abstract class BM_Page_Base {

	/**
	 * Slug of Bulk Delete plugin.
	 */
	const BULK_DELETE_SLUG = 'bulk-delete-posts';

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Page Title.
	 *
	 * @var string
	 */
	protected $page_title;

	/**
	 * Menu Title.
	 *
	 * @var string
	 */
	protected $menu_title;

	/**
	 * Warning message shown at the top of the page.
	 *
	 * @var string
	 */
	protected $warning_message;

	/**
	 * Capability needed to render the page.
	 *
	 * @var string
	 */
	protected $capability = 'manage_options';

	/**
	 * Metaboxes registered to this page.
	 *
	 * @var \BM_Metabox_Base[]
	 */
	protected $metaboxes = array();

	/**
	 * Current page.
	 *
	 * @var string
	 */
	protected $hook_suffix;

	/**
	 * Current screen.
	 *
	 * @var \WP_Screen
	 */
	protected $screen;

	/**
	 * Initialize values when an object is created.
	 *
	 * Subclasses override this method to initialize sub-class specific values.
	 *
	 * @return void
	 */
	abstract protected function initialize();

	/**
	 * BM_Page_Base constructor.
	 */
	public function __construct() {
		$this->initialize();
	}

	/**
	 * Register the page menu.
	 *
	 * This method will be invoked in `admin_menu` hook.
	 */
	public function register() {
		$this->register_page();
		$this->register_hooks();
		$this->register_metaboxes();
	}

	/**
	 * Register page.
	 */
	protected function register_page() {
		$hook_suffix = add_submenu_page(
			$this->get_base_page_slug(),
			$this->page_title,
			$this->menu_title,
			$this->capability,
			$this->slug,
			array( $this, 'render_page' )
		);

		if ( false !== $hook_suffix ) {
			$this->hook_suffix = $hook_suffix;
		}
	}

	/**
	 * Register hooks.
	 */
	protected function register_hooks() {
		add_action( 'admin_print_scripts-' . $this->hook_suffix, array( $this, 'enqueue_script' ) );
		add_action( 'admin_print_scripts-' . $this->hook_suffix, array( $this, 'enqueue_styles' ) );

		add_action( "load-{$this->hook_suffix}", array( $this, 'on_load_page' ) );

		add_action( 'admin_init', array( $this, 'verify_nonce' ) );

		add_filter( 'bm_plugin_action_links', array( $this, 'append_to_plugin_action_links' ) );
		add_filter( 'bm_metabox_user_meta_field', array( $this, 'modify_metabox_user_meta_field_if_bulk_delete_is_installed' ), 10, 2 );
	}

	/**
	 * Verify nonce.
	 */
	public function verify_nonce() {
		if ( ! isset( $_POST['bm_action'] ) ) {
			return;
		}

		if ( ! isset( $_POST[ $this->slug . '-nonce' ] ) ) {
			return;
		}

		check_admin_referer( $this->slug, $this->slug . '-nonce' );

		$action = sanitize_text_field( $_POST['bm_action'] );

		/**
		 * Before a request is handled.
		 */
		do_action( 'bm_pre_request_handler' );

		/**
		 * Trigger BM Action.
		 *
		 * Nonce check is already done.
		 */
		do_action( "bm_{$action}", $_POST );
	}

	/**
	 * Show the Admin page.
	 */
	public function render_page() {
		?>
		<div class="wrap">
			<h2><?php echo esc_html( $this->page_title ); ?></h2>
			<?php settings_errors(); ?>

			<form method="post">
				<?php
				wp_nonce_field( $this->slug, $this->slug . '-nonce' );

				/* Used to save closed meta boxes and their order */
				wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
				wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
				?>

				<div id = "poststuff">
					<div id="post-body" class="metabox-holder columns-1">

						<div id="post-body-content">
							<div class="notice notice-warning" >
								<p>
									<strong>
										<?php echo esc_html( $this->warning_message ); ?>
									</strong>
								</p>
							</div>
						</div><!-- #post-body-content -->

						<div id="postbox-container-2" class="postbox-container">
							<?php do_meta_boxes( '', 'advanced', null ); ?>
						</div> <!-- #postbox-container-2 -->

					</div> <!-- #post-body -->
				</div><!-- #poststuff -->

			</form>
		</div><!-- .wrap -->

		<?php
		add_filter( 'admin_footer_text', array( $this, 'modify_admin_footer_text' ) );
	}

	/**
	 * Modify the text that is displayed in footer of admin pages.
	 *
	 * TODO: Check the campaign parameters.
	 *
	 * @param string $footer_text Text that will be displayed in footer.
	 *
	 * @return string Modified footer text.
	 */
	public function modify_admin_footer_text( $footer_text ) {
		/* translators: 1 Bulk WP Site Link, Bulk Move Review Link */
		$footer_link = sprintf( __( 'Thank you for using <a href = "%1$s">Bulk Move</a> plugin! Kindly <a href = "%2$s">rate us</a> at <a href = "%2$s">WordPress.org</a>', 'bulk-delete' ),
			'http://bulkwp.com?utm_source=wpadmin&utm_campaign=BulkMove&utm_medium=footer',
			'http://wordpress.org/support/view/plugin-reviews/bulk-move?filter=5#postform'
		);

		$footer_link = apply_filters( 'bm_admin_page_footer_link', $footer_link );

		return str_replace( '</span>', '', $footer_text ) . ' | ' . $footer_link . '</span>';
	}

	/**
	 * Enqueue JavaScript.
	 */
	public function enqueue_script() {
		wp_enqueue_script( 'bulk-move', $this->get_plugin_dir_url() . 'assets/js/bulk-move.js', array( 'jquery', 'postbox' ), BM_BulkMove::VERSION, true );

		$msg = array(
			'move_warning' => __( 'Are you sure you want to move all the selected posts', 'bulk-move' ),
		);

		$error = array(
			'select_one' => __( 'Please select least one option', 'bulk-move' ),
		);

		$translation_array = array(
			'msg'   => $msg,
			'error' => $error,
		);

		/**
		 * Filters the localized JS translations.
		 *
		 * @param array $translation_array A key value pair.
		 */
		$translation_array = apply_filters( 'bm_javascript_array', $translation_array );

		wp_localize_script( 'bulk-move', 'BULK_MOVE', $translation_array );
	}

	/**
	 * Enqueue styles.
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'bulk-move', $this->get_plugin_dir_url() . 'assets/css/bulk-move.css', array(), BM_BulkMove::VERSION );
	}

	/**
	 * Triggered when the page is loaded.
	 */
	public function on_load_page() {
		$this->render_help_tab();

		// Trigger the add_meta_boxes hooks to allow meta boxes to be added.
		do_action( 'add_meta_boxes_' . $this->hook_suffix, null );
	}

	/**
	 * Render help tab.
	 *
	 * TODO: Handle page specific content.
	 */
	protected function render_help_tab() {
		$this->get_screen()->add_help_tab(
			array(
				'title'    => __( 'Bulk Move Posts', 'bulk-move' ),
				'id'       => 'about_tab',
				'content'  => '<p>' . __( 'This Plugin allows you to move posts from one category to another, from one tag to another and even from one custom taxonomy to another in bulk. This Plugin can also be used to remove assigned categories, tags and custom taxonomies from posts (includes custom post types).', 'bulk-move' ) . '</p>',
				'callback' => false,
			)
		);

		$this->get_screen()->set_help_sidebar(
			'<p><strong>' . __( 'More information', 'bulk-move' ) . '</strong></p>' .
			'<p><a href = "https://bulkwp.com/">' . __( 'Plugin Homepage/support', 'bulk-move' ) . '</a></p>' .
			'<p><a href = "http://sudarmuthu.com/blog">' . __( "Plugin author's blog", 'bulk-move' ) . '</a></p>' .
			'<p><a href = "http://sudarmuthu.com/wordpress/">' . __( "Other Plugin's by Author", 'bulk-move' ) . '</a></p>'
		);
	}

	/**
	 * Add a metabox to page.
	 *
	 * @param \BM_Metabox_Base $metabox Metabox to add.
	 */
	public function add_metabox( $metabox ) {
		if ( in_array( $metabox, $this->metaboxes ) ) {
			return;
		}

		$this->metaboxes[] = $metabox;
	}

	/**
	 * Load all the registered metaboxes.
	 */
	public function register_metaboxes() {
		foreach ( $this->metaboxes as $metabox ) {
			$metabox->register( $this->hook_suffix, $this->slug );
		}
	}

	/**
	 * Get the page slug of base page.
	 *
	 * @return string Page slug.
	 */
	public function get_base_page_slug() {
		if ( $this->is_bulkwp_menu_registered() ) {
			return self::BULK_DELETE_SLUG;
		}

		return 'bulk-move-posts';
	}

	/**
	 * Is the bulk wp menu already registered?
	 *
	 * @return bool True if registered, False otherwise.
	 */
	protected function is_bulkwp_menu_registered() {
		global $admin_page_hooks;

		return ! empty( $admin_page_hooks[ self::BULK_DELETE_SLUG ] );
	}

	/**
	 * Return the WP_Screen object for the current page's handle.
	 *
	 * @return \WP_Screen Screen object.
	 */
	public function get_screen() {
		if ( ! isset( $this->screen ) ) {
			$this->screen = WP_Screen::get( $this->hook_suffix );
		}

		return $this->screen;
	}

	/**
	 * Return the slug of the page.
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Get the hook suffix of the current page.
	 *
	 * @return string Hook suffix.
	 */
	public function get_hook_suffix() {
		return $this->hook_suffix;
	}

	/**
	 * Get the url to the plugin directory.
	 *
	 * @return string Url to plugin directory.
	 */
	protected function get_plugin_dir_url() {
		return plugin_dir_url( $this->get_plugin_file() );
	}

	/**
	 * Get the plugin main file.
	 *
	 * @return string Plugin main file
	 */
	protected function get_plugin_file() {
		$bulk_move = bulk_move();

		return $bulk_move->get_plugin_file();
	}

	/**
	 * Append link to the current page in plugin list.
	 *
	 * @param array $links Array of links.
	 *
	 * @return array Modified list of links.
	 */
	public function append_to_plugin_action_links( $links ) {
		$links[ $this->get_slug() ] = '<a href="admin.php?page=' . $this->get_slug() . '">' . $this->page_title . '</a>';

		return $links;
	}
	/**
	 * Modify the user meta field that determines if a metabox is hidden by user or not.
	 *
	 * This can change based on whether Bulk Delete plugin is installed or not.
	 *
	 * @param string $meta_field User Meta field.
	 * @param string $page_slug  Page Slug.
	 *
	 * @return string Modified user meta field.
	 */
	public function modify_metabox_user_meta_field_if_bulk_delete_is_installed( $meta_field, $page_slug ) {
		if ( $page_slug !== $this->slug ) {
			return $meta_field;
		}

		if ( $this->is_bulkwp_menu_registered() ) {
			return $meta_field;
		}

		// The meta field should be in the following form.
		// $meta_field = 'metaboxhidden_bulk-wp_page_' . $this->page_slug;
		return "metaboxhidden_bulk-wp_page_{$this->slug}";
	}
}
