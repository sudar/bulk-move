<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Bulk Move Posts admin page.
 *
 * @since 2.0.0
 */
class BM_Page_Post extends BM_Page_Base {

	protected function initialize() {
		$this->slug            = 'bulk-move-posts';
		$this->page_title      = __( 'Bulk Move Posts', 'bulk-move' );
		$this->menu_title      = __( 'Bulk Move Posts', 'bulk-move' );
		$this->warning_message = __( 'WARNING: Posts moved once cannot be reverted. Use with caution.', 'bulk-move' );
		$this->capability      = 'edit_others_posts';
	}

	public function register() {
		if ( ! $this->is_bulkwp_menu_registered() ) {
			$this->register_bulkwp_menu();
		}

		parent::register();

		add_filter( 'bm_plugin_action_links', array( $this, 'append_to_plugin_action_links' ) );
		add_filter( 'bm_metabox_user_meta_field', array( $this, 'modify_metabox_user_meta_field_if_bulk_delete_is_installed' ), 10, 2 );

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

		return 'metaboxhidden_toplevel_page_bulk-move-posts';
	}

	/**
	 * Register Bulk WP Menu.
	 */
	protected function register_bulkwp_menu() {
		add_menu_page(
			__( 'Bulk WP', 'bulk-delete' ),
			__( 'Bulk WP', 'bulk-delete' ),
			$this->capability,
			$this->slug,
			array( $this, 'render_page' ),
			'dashicons-trash'
		);
	}
}
