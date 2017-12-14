<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Bulk Move Posts admin page.
 *
 * @since 2.0.0
 */
class BM_Page_Post extends BM_Page_Base {

	protected function initialize() {
		$this->slug = 'bulk-move-posts';
		$this->page_title = __( 'Bulk Move Posts', 'bulk-move' );
		$this->menu_title = __( 'Bulk Move Posts', 'bulk-move' );
		$this->warning_message = __( 'WARNING: Posts moved once cannot be reverted. Use with caution.', 'bulk-move' );
		$this->capability = 'edit_others_posts';
	}

	public function register() {
		if ( ! $this->is_bulkwp_menu_registered() ) {
			$this->register_bulkwp_menu();
		}

		parent::register();

		add_filter( 'bm_plugin_action_links', array( $this, 'append_to_plugin_action_links' ) );
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
