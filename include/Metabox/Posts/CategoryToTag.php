<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Bulk Move Posts Category To Tag Metabox.
 *
 * @since 2.0.0
 */
class BM_Metabox_Posts_CategoryToTag extends BM_Metabox_PostBase {

	protected function initialize() {
		$this->meta_box_slug         = 'bm-posts-category-to-tag';
		$this->messages['box_label'] = __( 'Move Posts from Category To Tag', 'bulk-move' );
		$this->action                = 'move_category_to_tag';
	}

	public function render() {
		$tags = $this->get_tags_or_fail();

		if ( empty( $tags ) ) {
			return;
		}
		?>

        <!-- Category To Tag Start-->
        <h4><?php _e( 'On the left side, select the category whose post you want to move. In the right side select the tag to which you want the posts to be moved.', 'bulk-move' ); ?></h4>

        <fieldset class="options">
            <table class="optiontable">
                <tr>
                    <td scope="row">
                    	<?php
                        $categories = get_categories( array(
                                'hide_empty' => false,
                            )
                        );

                        if( count($categories) > 50 ){?>
                            <select class="select2Ajax" name="smbm_mct_cat" data-term="category" data-placeholder="<?php _e( 'Select Category', 'bulk-move' ); ?>" style="width:300px">
                            </select>
                        <?php }else{?>
                            <select class="select2" name="smbm_mct_cat" data-placeholder="<?php _e( 'Select Category', 'bulk-move' ); ?>">
                            <?php foreach ( $categories as $category ) { ?>
                                <option value="<?php echo $category->cat_ID; ?>"><?php echo $category->cat_name, ' (', $category->count, ' ', __( 'Posts', 'bulk-move' ), ')'; ?></option>
                            <?php } ?>
                            </select>
                        <?php }
                        ?>
                        ==>
                    </td>
                    <td scope="row">
                    	<?php
                        $tags = get_tags();

                        if( count($tags) > 50 ){?>
                            <select class="select2Ajax" name="smbm_mct_tag" data-term="post_tag" data-placeholder="<?php _e( 'Select Tag', 'bulk-move' ); ?>" style="width:300px">
                            </select>
                        <?php }else{?>
                            <select class="select2" name="smbm_mct_tag" data-placeholder="<?php _e( 'Select Tag', 'bulk-move' ); ?>" style="width:300px">
                            <?php foreach ( $tags as $tag ) { ?>
                                <option value="<?php echo absint( $tag->term_id ); ?>"><?php echo $tag->name, ' (', $tag->count, ' ', __( 'Posts', 'bulk-move' ), ')'; ?></option>
                            <?php } ?>
                            </select>
                        <?php }
                        ?>
                    </td>
                </tr>

            </table>
            <p>
				<?php _e( 'If the post contains other tags, then', 'bulk-move' ); ?>
				<?php $this->render_overwrite_filters(); ?>
            </p>
        </fieldset>

		<?php $this->render_submit(); ?>

        <!-- Category To Tag end-->
		<?php
	}

	protected function convert_user_input_to_options( $request ) {
		$options = array();

		$options['cat']       = absint( $request['smbm_mct_cat'] );
		$options['tag']       = absint( $request['smbm_mct_tag'] );
		$options['overwrite'] = $this->process_overwrite_filter( $request );

		return $options;
	}

	public function move( $options ) {
		$wp_query = new WP_Query();

		$posts = $wp_query->query( array(
			'category__in' => array( $options['cat'] ),
			'post_type'    => 'post',
			'nopaging'     => 'true',
		) );

		foreach ( $posts as $post ) {
			$current_cats = array_diff( wp_get_post_categories( $post->ID ), array( $options['cat'] ) );
			$current_tags = wp_get_post_tags( $post->ID, array( 'fields' => 'ids' ) );

			if ( $options['overwrite'] ) {
				// Override is set, so remove all common tags.
				$current_tags = array();
			}

			$current_tags[] = $options['tag'];

			wp_set_post_tags( $post->ID, $current_tags );

			if ( count( $current_cats ) == 0 ) {
				$current_cats = array( get_option( 'default_category' ) );
			}

			wp_update_post( array(
				'ID'            => $post->ID,
				'post_category' => $current_cats,
			) );
		}

		return count( $posts );
	}

	protected function get_success_message( $posts_moved ) {
		/* translators: 1 Number of posts moved */
		return _n( 'Moved %d post from the selected category', 'Moved %d posts from the selected tag', $posts_moved, 'bulk-move' );
	}
}
