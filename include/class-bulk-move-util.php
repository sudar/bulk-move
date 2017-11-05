<?php
/**
 * Utility class.
 *
 * @author Sudar
 *
 * @since 1.1
 */
class Bulk_Move_Util {

	// Meta boxes
	const VISIBLE_POST_BOXES = 'metaboxhidden_tools_page_bulk-move-posts';

	/**
	 * Check whether the meta box in posts page is hidden or not.
	 *
	 * @param $box
	 *
	 * @return (bool) whether the box is hidden or not
	 *
	 * @since 1.1
	 */
	public static function is_posts_box_hidden( $box ) {
		$hidden_boxes = self::get_posts_hidden_boxes();

		return ( is_array( $hidden_boxes ) && in_array( $box, $hidden_boxes ) );
	}

	/**
	 * Get the list of hidden boxes in posts page.
	 *
	 * @return the array of hidden meta boxes
	 *
	 * @since 1.1
	 */
	public static function get_posts_hidden_boxes() {
		$current_user = wp_get_current_user();

		return get_user_meta( $current_user->ID, self::VISIBLE_POST_BOXES, true );
	}

}

/**
 * Get the list of tags or bail out with a error message if no tags are found.
 *
 * @since 1.3.0
 *
 * @return array List of tags.
 */
function bm_get_tags_or_fail() {
	$tags = get_tags( array( 'hide_empty' => false ) );
?>

	<?php if ( empty( $tags ) ) : ?>
		<h4>
			<?php _e( 'There are no tags present. Add some tags to move posts based on tags.', 'bulk-move' ); ?>
		</h4>
	<?php endif; ?>

<?php
	return $tags;
}

/**
 * Render Tags Dropdown.
 *
 * @param string $name             Name for the dropdown.
 * @param array  $tags             Array of 'post_tag' term objects.
 * @param bool   $show_option_none Optional. Should the none option be added? Default false.
 */
function bm_render_tags_dropdown( $name, $tags, $show_option_none = false ) {
?>
	<select name="<?php echo esc_attr( $name ); ?>">
		<?php if ( $show_option_none ) : ?>
			<option value="-1"><?php _e( 'Remove Tag', 'bulk-move' ); ?></option>
		<?php endif; ?>

		<?php foreach ( $tags as $tag ) : ?>
			<option value="<?php echo esc_attr( $tag->term_id ); ?>">
				<?php echo esc_html( $tag->name ); ?> (<?php echo absint( $tag->count ), ' ', esc_html__( 'Posts', 'bulk-move' ); ?>)
			</option>
		<?php endforeach; ?>
	</select>
<?php
}
