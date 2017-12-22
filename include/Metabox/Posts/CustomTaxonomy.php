<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Metabox to move posts based on category Class.
 *
 * @since 2.0.0
 */
class BM_Metabox_Posts_CustomTaxonomy extends BM_Metabox_Base {

	protected function initialize() {
		$this->meta_box_slug = 'bm-posts-by-custom-taxonomy';
		$this->messages['box_label'] = __( 'Move Posts By Custom Taxonomy', 'bulk-move' );
		$this->action = 'move_custom_taxonomy';
	}

	public function render() {
		?>

		<!-- Custom Taxonomy Start-->

		<fieldset class="options">
			<table class="optiontable">
				<tr>
					<td scope="row" colspan="2">
						<?php _e( 'Select the post type to show its custom taxonomy.', 'bulk-move' ); ?>
					</td>
					<td scope="row">
				</tr>

				<tr>
					<td scope="row" colspan="2">
						<select name="smbm_mbct_post_type" id="smbm_mbct_post_type">
							<option value="-1"><?php _e( 'Select Post type', 'bulk-move' ); ?></option>

							<?php
							$custom_post_types = get_post_types( array( 'public' => true ) );
							?>

							<?php foreach ( $custom_post_types as $post_type ) : ?>
								<option value="<?php echo esc_attr( $post_type ); ?>"><?php echo esc_html( $post_type ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>

				<tr class="taxonomy-select-row">
					<td scope="row" colspan="2">
						<?php _e( 'Select taxonomy to show its terms.', 'bulk-move' ); ?>
					</td>
					<td scope="row">
				</tr>

				<tr class="taxonomy-select-row">
					<td scope="row" colspan="2">
						<select name="smbm_mbct_taxonomy" id="smbm_mbct_taxonomy">
							<option value="select"><?php _e( 'Select Taxonomy', 'bulk-move' ); ?></option>
						</select>
					</td>
				</tr>

				<tr class="term-select-row">
					<td scope="row" colspan="2">
						<?php _e( 'Select terms to move its posts.', 'bulk-move' ); ?>
					</td>
					<td scope="row">
				</tr>

				<tr class="term-select-row">
					<td scope="row" >
						<select name="smbm_mbct_selected_term" id="smbm_mbct_selected_term" class="postform">
							<option class="level-0" value="-1"><?php _e( ' Select Term&nbsp;&nbsp;', 'bulk-move' ); ?></option>
						</select>
						==>
					</td>
					<td scope="row" >
						<select name="smbm_mbct_mapped_term" id="smbm_mbct_mapped_term" class="postform">
							<option class="level-0" value="-1"><?php _e( 'Remove Term&nbsp;&nbsp;', 'bulk-move' ); ?></option>
						</select>
					</td>
				</tr>
			</table>

			<p class="bm_ct_filters">
				<?php _e( 'If the post contains other terms, then', 'bulk-move' ); ?>
				<?php $this->render_overwrite_filters(); ?>
			</p>

		</fieldset>

		<p class="submit bm_ct_submit">
			<button type="submit" name="bm_action" value="move_custom_taxonomy" class="button-primary"><?php _e( 'Bulk Move ', 'bulk-move' ); ?>&raquo;</button>
		</p>

		<!-- Custom Taxonomy end-->

		<?php
	}

	protected function convert_user_input_to_options( $request ) {
		$options = array();

		$options['old_term']   = absint( $request['smbm_mbct_selected_term'] );
		$options['taxonomy']   = $request['smbm_mbct_taxonomy'];
		$options['post_types'] = array( $request['smbm_mbct_post_type'] );

		$options['new_term'] = ( -1 === $request['smbm_mbct_mapped_term'] ) ? -1 : absint( $_POST['smbm_mbct_mapped_term'] );
		$options['overwrite'] = $this->process_overwrite_filter( $request );

		return $options;
	}

	public function move( $options ) {

		$wp_query    = new WP_Query();
		$posts_count = 0;

		if ( - 1 === $options['old_term'] ) {
			return $posts_count;
		}

		if ( ! is_array( $options['post_types'] ) ) {
		    return $posts_count;
		}

		foreach ( $options['post_types'] as $post_type ) {

			$posts = $wp_query->query(
				array(
					'tax_query' => array(
						array(
							'taxonomy' => $options['taxonomy'],
							'field'    => 'term_id',
							'terms'    => $options['old_term'],
						),
					),
					'post_type' => $post_type,
					'nopaging'  => 'true',
				)
			);

			foreach ( $posts as $post ) {
				if ( -1 === $options['new_term'] ) {
					wp_remove_object_terms( $post->ID, $options['old_term'], $options['taxonomy'] );
				} else {
					wp_set_object_terms( $post->ID, $options['new_term'], $options['taxonomy'], ! $options['overwrite'] );
				}

				$posts_count ++;
			}
		}

		return $posts_count;
	}

	protected function get_success_message( $posts_moved ) {
		/* translators: 1 Number of posts moved */
		return _n( 'Moved %d post from the selected term', 'Moved %d posts from the selected term', $posts_moved, 'bulk-move' );
	}
}
