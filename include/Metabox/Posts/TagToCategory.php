<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Bulk Move Posts Tag To Category Metabox.
 *
 * @since 2.0.0
 */
class BM_Metabox_Posts_TagToCategory extends BM_Metabox_PostBase {

    protected function initialize() {
        $this->meta_box_slug         = 'bm-posts-tag-to-category';
        $this->messages['box_label'] = __( 'Move Posts from Tag to Category', 'bulk-move' );
        $this->action                = 'move_tag_to_category';
    }

    public function render() {
        $tags = $this->get_tags_or_fail();

        if ( empty( $tags ) ) {
            return;
        }
        ?>

        <!-- Tag To Category Start-->
        <h4><?php _e( 'On the left side, select the tag whose post you want to move. In the right side select the category to which you want the posts to be moved.', 'bulk-move' ); ?></h4>

        <fieldset class="options">
            <table class="optiontable">
                <tr>
                    <td scope="row" >
                        <?php $this->render_tags_dropdown( 'smbm_mt_tag', $tags ); ?>
                        ==>
                    </td>
                    <td scope="row" >
                        <?php
                        wp_dropdown_categories( array(
                            'name'             => 'smbm_mc_mapped_cat',
                            'show_count'       => true,
                            'hierarchical'     => true,
                            'orderby'          => 'NAME',
                            'hide_empty'       => false,
                        ) );
                        ?>
                    </td>
                </tr>

            </table>
            <p>
                <?php _e( 'If the post contains other categories', 'bulk-move' ); ?>
                <?php $this->render_overwrite_filters(); ?>
            </p>
        </fieldset>

        <?php $this->render_submit(); ?>

        <!-- Tag To Category end-->
        <?php
    }

    protected function convert_user_input_to_options( $request ) {
        $options = array();

        $options['tag']       = absint( $request['smbm_mt_tag'] );
        $options['cat']       =  absint( $request['smbm_mc_mapped_cat'] );
        $options['overwrite'] = $this->process_overwrite_filter( $request );

        return $options;
    }

    public function move( $options ) {
        $wp_query = new WP_Query();

        $posts = $wp_query->query( array(
            'tag__in'   => $options['tag'],
            'post_type' => 'post',
            'nopaging'  => 'true',
        ));

        foreach ( $posts as $post ) {
            $current_tags = wp_get_post_tags( $post->ID, array( 'fields' => 'ids' ) );
            $current_tags = array_diff( $current_tags, array( $options['tag'] ) );

            $current_cats = wp_get_post_categories( $post->ID );
	        $current_cats[]  = $options['cat'];
            if ( $options['overwrite'] ) {
                // Override is set, so remove all common tags.
	            $current_cats = array();
            }

            $current_tags = array_values( $current_tags );
            wp_set_post_tags( $post->ID, $current_tags );

	        if ( count( $current_cats ) == 0 ) {
		        $current_cats = array( $options['cat'] );
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
        return _n( 'Moved %d post from the selected tag', 'Moved %d posts from the selected tag', $posts_moved, 'bulk-move' );
    }
}
