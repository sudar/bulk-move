<?php

/**
 * Custom Taxonomy select2 Ajax Action Class.
 *
 * @since 2.0.0
 */
class BM_Request_LoadTaxonomyTerm {

	/**
	 * Entry point for the BM_Request_LoadTaxonomyTerm class.
	 *
	 * @since 2.0.0
	 */
	public function load() {
		add_action( 'wp_ajax_bm_load_taxonomy_term', array( $this, 'bm_load_taxonomy_term' ) );
		add_filter( 'bm_javascript_array', array( $this, 'include_ajax_params_in_localization' ) );
	}

	/**
	 * Ajax call back function for getting taxonomies to load select2 options.
	 *
	 * @since 2.0.0
	 */
	public function bm_load_taxonomy_term(){
		check_ajax_referer( 'bulk-move-posts', 'nonce' );
		$return = array();

		$terms = get_terms( array(
			'taxonomy'   => sanitize_text_field($_GET['term']),
			'hide_empty' => false,
			'search'     => sanitize_text_field($_GET['q']),
		) );

		foreach ( $terms as $term ) {
			$return[] = array( absint($term->term_id), $term->name . ' (' . $term->count . __( ' Posts', 'bulk-move' ) . ')' );
		}

		echo json_encode( $return );
		die;
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
			'load_taxonomy_action' => 'bm_load_taxonomy_term',
			'nonce'                => wp_create_nonce( 'bulk-move-posts' ),
		);

		$translation_array['bulk_move_posts_taxonomy'] = $bulk_move_posts;

		return $translation_array;
	}
}
