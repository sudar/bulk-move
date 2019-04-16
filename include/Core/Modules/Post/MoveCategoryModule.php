<?php

namespace BulkWP\BulkMove\Core\Modules\Post;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Metabox to move posts based on category.
 *
 * @since 2.0.0
 */
class MoveCategoryModule extends PostModule {

	protected function initialize() {
		$this->meta_box_slug         = 'bm-posts-by-category';
		$this->messages['box_label'] = __( 'Move Posts By Category', 'bulk-move' );
		$this->action                = 'move_category';
	}

	public function render() {
		?>

		<!-- Category Start-->
		<h4>
			<?php
			_e( 'On the left side, select the category whose post you want to move.', 'bulk-move' );
			_e( 'In the right side select the category to which you want the posts to be moved.', 'bulk-move' );
			?>
		</h4>

		<fieldset class="options">
			<table class="optiontable">
				<tr>
					<td scope="row">
						<?php $this->render_categories_dropdown( 'smbm_mc_selected_cat' ); ?>
						==>
					</td>
					<td scope="row">
						<?php $this->render_categories_dropdown( 'smbm_mc_mapped_cat' ); ?>
					</td>
				</tr>
			</table>

			<p>
				<?php _e( 'If the post contains other categories, then', 'bulk-move' ); ?>
				<?php $this->render_overwrite_filters(); ?>
			</p>

		</fieldset>

		<?php $this->render_submit(); ?>

		<!-- Category end-->

		<?php
	}

	protected function convert_user_input_to_options( $request ) {
		$options = array();

		$options['old_cat']   = absint( $request['smbm_mc_selected_cat'] );
		$options['new_cat']   = ( '-1' === $request['smbm_mc_mapped_cat'] ) ? -1 : absint( $request['smbm_mc_mapped_cat'] );
		$options['overwrite'] = $this->process_overwrite_filter( $request );

		return $options;
	}

	public function move( $options ) {
		$wp_query = new \WP_Query();
		$posts    = $wp_query->query( array(
			'category__in' => array( $options['old_cat'] ),
			'post_type'    => 'post',
			'nopaging'     => 'true',
			'post_status'  => 'publish',
		) );

		foreach ( $posts as $post ) {
			$current_cats = wp_get_post_categories( $post->ID );

			if ( $current_cats instanceof \WP_Error || ( ! is_array( $current_cats ) ) ) {
				continue;
			}

			if ( $options['overwrite'] ) {
				// Override is set, so remove all common categories.
				$current_cats = array();
			}

			if ( -1 === $options['new_cat'] ) {
				$current_cats = array_diff( $current_cats, array( $options['old_cat'] ) );
			} else {
				$current_cats[] = $options['new_cat'];
			}

			if ( count( $current_cats ) == 0 ) {
				$current_cats = array( get_option( 'default_category' ) );
			}
			$current_cats = array_values( $current_cats );
			wp_update_post( array(
				'ID'            => $post->ID,
				'post_category' => $current_cats,
			) );
		}

		return count( $posts );
	}

	protected function get_success_message( $posts_moved ) {
		/* translators: 1 Number of posts moved */
		return _n( 'Moved %d post from the selected category', 'Moved %d posts from the selected category', $posts_moved, 'bulk-move' );
	}
}
