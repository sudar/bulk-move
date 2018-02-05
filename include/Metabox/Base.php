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
	 * @var string Slug of the meta box.
	 */
	protected $meta_box_slug;

	/**
	 * @var string Action in which the move operation should be performed.
	 */
	protected $action;

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
	abstract protected function render();

	/**
	 * Process user input and create metabox options.
	 *
	 * @abstract
	 *
	 * @param array $request Request array.
	 *
	 * @return array User options.
	 */
	abstract protected function convert_user_input_to_options( $request );

	/**
	 * Move items.
	 *
	 * @abstract
	 *
	 * @param array $options User selected options.
	 *
	 * @return int Number of items deleted
	 */
	abstract public function move( $options );

	/**
	 * Get Success Message.
	 *
	 * @param int $posts_moved Number of posts that were moved.
	 *
	 * @return string Success message.
	 */
	abstract protected function get_success_message( $posts_moved );

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
	 * @param string $page_slug   Page slug.
	 */
	public function register( $hook_suffix, $page_slug ) {
		$this->page_hook_suffix = $hook_suffix;
		$this->page_slug        = $page_slug;

		add_action( "add_meta_boxes_{$this->page_hook_suffix}", array( $this, 'setup_metabox' ) );

		add_action( 'bm_' . $this->action, array( $this, 'process' ) );
		add_filter( 'bm_javascript_array', array( $this, 'filter_js_array' ) );
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
	 * Process the metabox.
	 *
	 * @param array $request Request array.
	 */
	public function process( $request ) {
		$options     = $this->convert_user_input_to_options( $request );
		$posts_moved = $this->move( $options );
		$this->render_result( $posts_moved );
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
		$meta_field = 'metaboxhidden_toplevel_page_' . $this->page_slug;

		/**
		 * Filter the user meta field that determines whether a metabox is hidden or not.
		 *
		 * @since 2.0.0
		 *
		 * @param string User Meta Field name.
		 * @param string Page Slug.
		 */
		return apply_filters( 'bm_metabox_user_meta_field', $meta_field, $this->page_slug );
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

	/**
	 * Process overwrite filter.
	 *
	 * @param array $request Request array.
	 *
	 * @return bool True if overwrite is enabled, false otherwise.
	 */
	protected function process_overwrite_filter( $request ) {
		return isset( $request[ $this->action . '_overwrite' ] ) && 'overwrite' === $request[ $this->action . '_overwrite' ];
	}

	/**
	 * Get the list of tags or bail out with a error message if no tags are found.
	 *
	 * Ideally this method should be included in a trait. This is added here since PHP 5.2 needs to be supported.
	 *
	 * @since 1.3.0
	 *
	 * @return array List of tags.
	 */
	protected function get_tags_or_fail() {
		$tags = get_tags( array( 'hide_empty' => false ) );
		?>

		<?php if ( empty( $tags ) ) : ?>
			<h4>
				<?php _e( 'There are no tags present. Add some tags to move posts based on tags.', 'bulk-move' ); ?>
			</h4>
		<?php endif; ?>

		<?php
		return $tags;
	}

	/**
	 * Render Tags Dropdown.
	 *
	 * Ideally this method should be included in a trait. This is added here since PHP 5.2 needs to be supported.
	 *
	 * @param string $name             Name for the dropdown.
	 * @param array  $tags             Array of 'post_tag' term objects.
	 * @param bool   $show_option_none Optional. Should the none option be added? Default false.
	 */
	protected function render_tags_dropdown( $name, $tags, $show_option_none = false ) {
		?>
		<select name="<?php echo esc_attr( $name ); ?>">
			<?php if ( $show_option_none ) : ?>
				<option value="-1"><?php _e( 'Remove Tag', 'bulk-move' ); ?></option>
			<?php endif; ?>

			<?php foreach ( $tags as $tag ) : ?>
				<option value="<?php echo esc_attr( $tag->term_id ); ?>">
					<?php echo esc_html( $tag->name ); ?> (<?php echo absint( $tag->count ), ' ', esc_html__( 'Posts', 'bulk-move' ); ?>)
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render Overwrite Filter options.
	 */
	protected function render_overwrite_filters() {
		?>
		<input type="radio" name="<?php echo esc_attr( $this->action ); ?>_overwrite" value="overwrite"
			   checked><?php _e( 'Remove them', 'bulk-move' ); ?>
		<input type="radio" name="<?php echo esc_attr( $this->action ); ?>_overwrite"
			   value="no-overwrite"><?php _e( "Don't remove them", 'bulk-move' ); ?>
		<?php
	}

	/**
	 * Render submit button.
	 */
	protected function render_submit() {
		?>

		<p class="submit">
			<button type="submit" name="bm_action" value="<?php echo esc_attr( $this->action ); ?>"
					class="button-primary">
				<?php _e( 'Bulk Move ', 'bulk-move' ); ?>&raquo;
			</button>
		</p>

		<?php
	}

	/**
	 * Render results.
	 *
	 * @param int $posts_moved Number of posts that were moved.
	 */
	protected function render_result( $posts_moved ) {
		$msg = sprintf( $this->get_success_message( $posts_moved ), $posts_moved );

		add_settings_error(
			$this->page_slug,
			$this->action,
			$msg,
			'updated'
		);
	}

	/**
	 * Render the post type dropdown.
	 *
	 * All the public post types are listed in the dropdown.
	 *
	 * @since 2.0.0
	 */
	protected function render_post_type_dropdown() {
	?>
		<select name="smbm_<?php echo esc_attr( $this->action ); ?>_post_type" id="smbm_<?php echo esc_attr( $this->action ); ?>_post_type">
			<option value="-1"><?php _e( 'Select Post type', 'bulk-move' ); ?></option>

			<?php
			$custom_post_types = get_post_types( array( 'public' => true ) );
			?>

			<?php foreach ( $custom_post_types as $post_type ) : ?>
				<option value="<?php echo esc_attr( $post_type ); ?>"><?php echo esc_html( $post_type ); ?></option>
			<?php endforeach; ?>
		</select>
	<?php
	}
}
