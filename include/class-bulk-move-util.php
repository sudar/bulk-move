<?php
/**
 * Utility class
 *
 * @package Bulk Move
 * @author Sudar
 * @since 1.1
 */
class Bulk_Move_Util {

    // Meta boxes
    const VISIBLE_POST_BOXES     = 'metaboxhidden_tools_page_bulk-move-posts';

    /**
     * Check whether the meta box in posts page is hidden or not
     *
     * @param $box
     *
     * @return (bool) whether the box is hidden or not
     * @since 1.1
     */
    public static function is_posts_box_hidden( $box ) {
        $hidden_boxes = self::get_posts_hidden_boxes();
        return ( is_array( $hidden_boxes ) && in_array( $box, $hidden_boxes ) );
    }

    /**
     * Get the list of hidden boxes in posts page
     *
     * @return the array of hidden meta boxes
     * @since 1.1
     */
    public static function get_posts_hidden_boxes() {
        $current_user = wp_get_current_user();
        return get_user_meta( $current_user->ID, self::VISIBLE_POST_BOXES, TRUE );
    }
}
?>
