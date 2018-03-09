<?php

/**
 * Custom Taxonomy metabox's Ajax Action Class.
 *
 * @since 2.0.0
 */
class BM_Request_CustomTaxonomyAction {

	/**
	 * Entry point for the BM_Request_CustomTaxonomyAction class.
	 *
	 * @since 2.0.0
	 */
	public function load() {
		add_action( 'wp_ajax_load_custom_taxonomy_by_post_type', array( $this, 'load_custom_taxonomy_by_post_type' ) );
		add_action( 'wp_ajax_load_custom_terms_by_taxonomy', array( $this, 'load_custom_terms_by_taxonomy' ) );

		add_filter( 'bm_javascript_array', array( $this, 'include_ajax_params_in_localization' ) );
	}

	/**
	 * Loads the custom Taxonomy by Post Type.
	 *
	 * @since 2.0.0
	 */
	public function load_custom_taxonomy_by_post_type() {
		check_ajax_referer( 'bulk-move-posts', 'nonce' );

		$post_type  = isset( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : 'post';
		$taxonomies = get_object_taxonomies( $post_type );

		$no_taxonomy_message = sprintf( __( 'There are no taxonomies associated with "%s" post type.', 'bulk-move' ), $post_type );

		wp_send_json_success(
			array(
				'taxonomies'            => $taxonomies,
				'no_taxonomy_msg'       => $no_taxonomy_message,
				'select_taxonomy_label' => __( 'Select Taxonomy', 'bulk-move' ),
			)
		);
	}

	/**
	 * Loads the custom Terms by Taxonomy.
	 *
	 * @since 2.0.0
	 */
	public function load_custom_terms_by_taxonomy() {
		check_ajax_referer( 'bulk-move-posts', 'nonce' );

		$terms    = array();
		$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_text_field( $_POST['taxonomy'] ) : '';

		$wp_terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'orderby'    => 'name',
			)
		);

		if ( ! is_wp_error( $wp_terms ) ) {
			foreach ( $wp_terms as $wp_term ) {
				$terms[ $wp_term->term_id ] = array(
					'term_name'  => esc_html( $wp_term->name ),
					'term_count' => absint( $wp_term->count ),
				);
			}
		}

		$no_terms_message = sprintf( __( 'There are no terms associated with "%s" taxonomy.', 'bulk-move' ), $taxonomy );

		wp_send_json_success(
			array(
				'terms'             => $terms,
				'no_term_msg'       => $no_terms_message,
				'select_term_label' => __( 'Select Term', 'bulk-move' ),
				'remove_term_label' => __( 'Remove Term', 'bulk-move' ),
			)
		);
	}

	/**
	 * Includes the additional JS variables using the
	 * Bulk Move JS translation array.
	 *
	 * @since 2.0.0
	 *
	 * @param $translation_array
	 *
	 * @return array $translation_array
	 */
	public function include_ajax_params_in_localization( $translation_array ) {
		$bulk_move_posts = array(
			'get_taxonomy_action' => 'load_custom_taxonomy_by_post_type',
			'get_terms_action'    => 'load_custom_terms_by_taxonomy',
			'nonce'               => wp_create_nonce( 'bulk-move-posts' ),
		);

		$translation_array['bulk_move_posts'] = $bulk_move_posts;

		return $translation_array;
	}
}
