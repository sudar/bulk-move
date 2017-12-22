<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Bulk Move Posts by Tag Metabox.
 *
 * @since 2.0.0
 */
class BM_Metabox_Posts_Tag extends BM_Metabox_Base {

	protected function initialize() {
		$this->meta_box_slug         = 'bm-posts-by-tag';
		$this->messages['box_label'] = __( 'Move Posts By Tag', 'bulk-move' );
		$this->action                = 'move_tag';
	}

	public function render() {
		$tags = $this->get_tags_or_fail();

		if ( empty( $tags ) ) {
			return;
		}
		?>

		<!-- Tag Start-->
		<h4><?php _e( 'On the left side, select the tag whose post you want to move. In the right side select the tag to which you want the posts to be moved.', 'bulk-move' ); ?></h4>

		<fieldset class="options">
			<table class="optiontable">
				<tr>
					<td scope="row" >
						<?php $this->render_tags_dropdown( 'smbm_mt_old_tag', $tags ); ?>
						==>
					</td>
					<td scope="row" >
						<?php $this->render_tags_dropdown( 'smbm_mt_new_tag', $tags, true ); ?>
					</td>
				</tr>

			</table>
			<p>
				<?php _e( 'If the post contains other tags, then', 'bulk-move' ); ?>
				<?php $this->render_overwrite_filters(); ?>
			</p>
		</fieldset>

		<?php $this->render_submit(); ?>

		<!-- Tag end-->
		<?php
	}

	protected function convert_user_input_to_options( $request ) {
		$options = array();

		$options['old_tag']   = absint( $request['smbm_mt_old_tag'] );
		$options['new_tag']   = ( - 1 === $request['smbm_mt_new_tag'] ) ? - 1 : absint( $request['smbm_mt_new_tag'] );
		$options['overwrite'] = $this->process_overwrite_filter( $request );

		return $options;
	}

	public function move( $options ) {
		$wp_query = new WP_Query();

		$posts = $wp_query->query( array(
			'tag__in'   => $options['old_tag'],
			'post_type' => 'post',
			'nopaging'  => 'true',
		));

		foreach ( $posts as $post ) {
			$current_tags = wp_get_post_tags( $post->ID, array( 'fields' => 'ids' ) );
			$current_tags = array_diff( $current_tags, array( $options['old_tag'] ) );

			if ( - 1 !== $options['new_tag'] ) {
				if ( $options['overwrite'] ) {
					// Remove old tags.
					$current_tags = array( $options['new_tag'] );
				} else {
					// add to existing tags.
					$current_tags[] = $options['new_tag'];
				}
			}

			$current_tags = array_values( $current_tags );
			wp_set_post_tags( $post->ID, $current_tags );
		}

		return count( $posts );
	}

	protected function get_success_message( $posts_moved ) {
		/* translators: 1 Number of posts moved */
		return _n( 'Moved %d post from the selected tag', 'Moved %d posts from the selected tag', $posts_moved, 'bulk-move' );
	}
}
