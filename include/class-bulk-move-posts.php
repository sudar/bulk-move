<?php
/**
 * Utility class for moving posts
 *
 * @package Bulk Move
 * @since 1.0
 * @author Sudar
 */
class Bulk_Move_Posts {

    /**
     * Render move categories box
     *
     * @since 1.0
     */
    public static function render_move_category_box() {

        if ( Bulk_Move_Util::is_posts_box_hidden( Bulk_Move::BOX_CATEGORY ) ) {
            printf( __( 'This section just got enabled. Kindly <a href = "%1$s">refresh</a> the page to fully enable it.', 'bulk-move' ), 'tools.php?page=' . Bulk_Move::POSTS_PAGE_SLUG );
            return;
        }
?>
        <!-- Category Start-->
        <h4><?php _e( 'On the left side, select the category whose post you want to move. In the right side select the category to which you want the posts to be moved.', 'bulk-move' ) ?></h4>

        <fieldset class="options">
		<table class="optiontable">
            <tr>
                <td scope="row" >
                <select name="smbm_selected_cat">
<?php
        $categories =  get_categories(array( 'hide_empty' => false));
        foreach ($categories as $category) {
?>
                    <option value="<?php echo $category->cat_ID; ?>">
                    <?php echo $category->cat_name; ?> (<?php echo $category->count . " "; _e( "Posts", 'bulk-move' ); ?>)
                    </option>
<?php
        }
?>
                </select>
                ==>
                </td>
                <td scope="row" >
                <select name="smbm_mapped_cat">
                <option value="-1"><?php _e( "Remove Category", 'bulk-move' ); ?></option>
<?php
        foreach ($categories as $category) {
?>
                    <option value="<?php echo $category->cat_ID; ?>">
                    <?php echo $category->cat_name; ?> (<?php echo $category->count . " "; _e( "Posts", 'bulk-move' ); ?>)
                    </option>
<?php
        }
?>
                </select>
                </td>
            </tr>

		</table>
		</fieldset>
        <p class="submit">
            <button type="submit" name="smbm_action" value = "bulk-move-cats" class="button-primary"><?php _e( 'Bulk Move ', 'bulk-move' ) ?>&raquo;</button>
        </p>
        <!-- Category end-->
<?php
    }

    /**
     * Render move by tag box
     *
     * @since 1.1
     * @static
     * @access public
     */
    public static function render_move_tag_box() {

        if ( Bulk_Move_Util::is_posts_box_hidden( Bulk_Move::BOX_TAG ) ) {
            printf( __( 'This section just got enabled. Kindly <a href = "%1$s">refresh</a> the page to fully enable it.', 'bulk-move' ), 'tools.php?page=' . Bulk_Move::POSTS_PAGE_SLUG );
            return;
        }
?>
        <!-- Tag Start-->
        <h4><?php _e( 'On the left side, select the tag whose post you want to move. In the right side select the tag to which you want the posts to be moved.', 'bulk-move' ) ?></h4>

        <fieldset class="options">
		<table class="optiontable">
            <tr>
                <td scope="row" >
                <select name="smbm_old_tag">
<?php
                $tags =  get_tags( array( 'hide_empty' => false ) );
                foreach ( $tags as $tag ) {
?>
                    <option value="<?php echo $tag->term_id; ?>">
                    <?php echo $tag->name; ?> (<?php echo $tag->count . ' '; _e( 'Posts', 'bulk-move' ); ?>)
                    </option>
<?php
                }
?>
                </select>
                ==>
                </td>
                <td scope="row" >
                <select name="smbm_new_tag">
                    <option value="-1"><?php _e( 'Remove Tag', 'bulk-move' ); ?></option>
<?php
                foreach ($tags as $tag) {
?>
                    <option value="<?php echo $tag->term_id; ?>">
                        <?php echo $tag->name; ?> (<?php echo $tag->count . ' '; _e( 'Posts', 'bulk-move' ); ?>)
                    </option>
<?php
                }
?>
                </select>
                </td>
            </tr>

		</table>
		</fieldset>
        <p class="submit">
            <button type="submit" name="smbm_action" value = "bulk-move-tags" class="button-primary"><?php _e( 'Bulk Move ', 'bulk-move' ) ?>&raquo;</button>
        </p>
        <!-- Tag end-->
<?php
    }

    /**
     * Render debug box
     *
     * @since 1.0
     */
    public static function render_debug_box() {
?>
        <!-- Debug box start-->
        <p>
            <?php _e( 'If you are seeing a blank page after clicking the Bulk Move button, then ', 'bulk-move' ); ?>
                <a href = "http://sudarmuthu.com/wordpress/bulk-move#faq"><?php _e( 'check out this FAQ', 'bulk-move' );?></a>. 
            <?php _e( 'You also need need the following debug information.', 'bulk-move' ); ?>
        </p>
        <table cellspacing="10">
            <tr>
                <th align = "right"><?php _e( 'PHP Version ', 'bulk-move' ); ?></th>
                <td><?php echo phpversion(); ?></td>
            </tr>
            <tr>
                <th align = "right"><?php _e( 'Plugin Version ', 'bulk-move' ); ?></th>
                <td><?php echo Bulk_move::VERSION; ?></td>
            </tr>
            <tr>
                <th align = "right"><?php _e( 'Available memory size ', 'bulk-move' );?></th>
                <td><?php echo ini_get( 'memory_limit' ); ?></td>
            </tr>
            <tr>
                <th align = "right"><?php _e( 'Script time out ', 'bulk-move' );?></th>
                <td><?php echo ini_get( 'max_execution_time' ); ?></td>
            </tr>
            <tr>
                <th align = "right"><?php _e( 'Script input time ', 'bulk-move' ); ?></th>
                <td><?php echo ini_get( 'max_input_time' ); ?></td>
            </tr>
        </table>

        <p><em><?php _e( "If you are looking to move posts in bulk, try out my ", 'bulk-move' ); ?> <a href = "http://sudarmuthu.com/wordpress/bulk-move"><?php _e( "Bulk move Plugin", 'bulk-move' );?></a>.</em></p>
        <!-- Debug box end-->
<?php
    }
}
?>
