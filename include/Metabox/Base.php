<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * A Bulk Move Metabox module that is embedded inside a page.
 *
 * Create a subclass to create metabox modules.
 *
 * @since 2.0.0
 */
abstract class BM_Metabox_Base {
	/**
	 * @var string Item Type. Possible values 'posts', 'pages', 'users' etc.
	 */
	protected $item_type = 'posts';

	/**
	 * The hook_suffix of the admin page in which this module is embedded.
	 *
	 * @var string
	 */
	protected $page_hook_suffix;

	/**
	 * @var string Slug of the page where this module will be shown.
	 */
	protected $page_slug;

	/**
	 * @var string Slug for the form fields.
	 */
	protected $field_slug;

	/**
	 * @var string Slug of the meta box.
	 */
	protected $meta_box_slug;

	/**
	 * @var string Hook in which this meta box should be shown.
	 */
	protected $meta_box_hook;

	/**
	 * @var string Action in which the move operation should be performed.
	 */
	protected $action;

	/**
	 * @var string Hook for scheduler.
	 */
	protected $cron_hook;

	/**
	 * @var string Url of the scheduler addon.
	 */
	protected $scheduler_url;

	/**
	 * @var array Messages shown to the user.
	 */
	protected $messages = array();

	/**
	 * Initialize and setup variables.
	 *
	 * @abstract
	 *
	 * @return void
	 */
	abstract protected function initialize();

	/**
	 * Initialize and setup variables.
	 *
	 * @abstract
	 *
	 * @return void
	 */
	abstract public function render();

	/**
	 * Process the deletion.
	 *
	 * @abstract
	 *
	 * @param array $request Request array.
	 *
	 * @return void
	 */
	abstract public function process( $request );

	/**
	 * Move items.
	 *
	 * @abstract
	 *
	 * @param array $options User selected options.
	 * @return int Number of items deleted
	 */
	abstract public function move( $options );

	/**
	 * Base constructor.
	 */
	public function __construct() {
		$this->initialize();
	}

	/**
	 * Register.
	 *
	 * @param string $hook_suffix Page Hook Suffix.
	 * @param string $page_slug Page slug.
	 */
	public function register( $hook_suffix, $page_slug ) {
		$this->page_hook_suffix = $hook_suffix;
		$this->page_slug        = $page_slug;

		add_action( "add_meta_boxes_{$this->page_hook_suffix}", array( $this, 'setup_metabox' ) );

		add_action( 'bm_' . $this->action, array( $this, 'process' ) );
		add_filter( 'bd_javascript_array', array( $this, 'filter_js_array' ) );
	}

	/**
	 * Setup the meta box.
	 */
	public function setup_metabox() {
		add_meta_box(
			$this->meta_box_slug,
			$this->messages['box_label'],
			array( $this, 'render_box' ),
			$this->page_hook_suffix,
			'advanced'
		);
	}

	/**
	 * Render the meta box if it's not hidden.
	 */
	public function render_box() {
		if ( $this->is_hidden() ) {
			printf(
				/* translators: 1 module url */
				__( 'This section just got enabled. Kindly <a href = "%1$s">refresh</a> the page to fully enable it.', 'bulk-move' ),
				'admin.php?page=' . $this->page_slug
			);

			return;
		}

		$this->render();
	}

	/**
	 * Is the current meta box hidden by user.
	 *
	 * @return bool True, if hidden. False, otherwise.
	 */
	protected function is_hidden() {
		$current_user    = wp_get_current_user();
		$user_meta_field = $this->get_hidden_box_user_meta_field();
		$hidden_boxes    = get_user_meta( $current_user->ID, $user_meta_field, true );

		return is_array( $hidden_boxes ) && in_array( $this->meta_box_slug, $hidden_boxes );
	}

	/**
	 * Get the user meta field that stores the status of the hidden meta boxes.
	 *
	 * @since 5.5
	 *
	 * @return string Name of the User Meta field.
	 */
	protected function get_hidden_box_user_meta_field() {
		if ( 'posts' == $this->item_type ) {
			return 'metaboxhidden_toplevel_page_bulk-delete-posts';
		} else {
			return 'metaboxhidden_bulk-wp_page_' . $this->page_slug;
		}
	}

	/**
	 * Filter the js array.
	 * This function will be overridden by the child classes.
	 *
	 * @since 5.5
	 *
	 * @param array $js_array JavaScript Array.
	 *
	 * @return array Modified JavaScript Array
	 */
	public function filter_js_array( $js_array ) {
		return $js_array;
	}
}