<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Bulk Move Users admin page.
 *
 * @since 2.0.0
 */
class BM_Page_User extends BM_Page_Base {

	protected function initialize() {
		$this->slug            = 'bulk-move-users';
		$this->page_title      = __( 'Bulk Move Users', 'bulk-move' );
		$this->menu_title      = __( 'Bulk Move Users', 'bulk-move' );
		$this->warning_message = __( 'WARNING: Users moved once cannot be reverted. Use with caution.', 'bulk-move' );
		$this->capability      = 'edit_others_posts';
	}

	public function register() {
		parent::register();

		add_filter( 'bm_metabox_user_meta_field', array( $this, 'modify_metabox_user_meta_field_if_bulk_delete_is_installed' ), 10, 2 );
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
		return 'metaboxhidden_bulk-wp_page_bulk-move-users';
	}
}
