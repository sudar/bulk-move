<?php
/**
 * Utility class for moving posts.
 *
 * @since   1.0
 *
 * @author  Sudar
 */
class Bulk_Move_Posts {

	/**
	 * Render move categories box.
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
		<h4><?php _e( 'On the left side, select the category whose post you want to move. In the right side select the category to which you want the posts to be moved.', 'bulk-move' ); ?></h4>

		<fieldset class="options">
			<table class="optiontable">
				<tr>
					<td scope="row" >
						<?php
						wp_dropdown_categories( array(
							'name'         => 'smbm_mc_selected_cat',
							'show_count'   => true,
							'hierarchical' => true,
							'orderby'      => 'NAME',
							'hide_empty'   => false,
						) );
						?>
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
							'show_option_none' => __( 'Remove Category', 'bulk-move' ),
						) );
						?>
					</td>
				</tr>

			</table>
			<p>
				<?php _e( 'If the post contains other categories, then', 'bulk-move' ); ?>
				<input type="radio" name="smbm_mc_overwrite" value="overwrite" checked><?php _e( 'Remove them', 'bulk-move' ); ?>
				<input type="radio" name="smbm_mc_overwrite" value="no-overwrite"><?php _e( "Don't remove them", 'bulk-move' ); ?>
			</p>

		</fieldset>
		<p class="submit">
			<button type="submit" name="bm_action" value="move_cats" class="button-primary"><?php _e( 'Bulk Move ', 'bulk-move' ); ?>&raquo;</button>
		</p>
		<!-- Category end-->
		<?php
	}

	/**
	 * Move posts from one category to another.
	 *
	 * @static
	 * @access public
	 *
	 * @since  1.2.0
	 */
	public static function move_cats() {
		if ( check_admin_referer( 'sm-bulk-move-posts', 'sm-bulk-move-posts-nonce' ) ) {

			do_action( 'bm_pre_request_handler' );

			$wp_query = new WP_Query();
			$bm       = BULK_MOVE();

			// move by cats.
			$old_cat = absint( $_POST['smbm_mc_selected_cat'] );
			$new_cat = ( -1 === $_POST['smbm_mc_mapped_cat'] ) ? -1 : absint( $_POST['smbm_mc_mapped_cat'] );

			$posts = $wp_query->query(array(
				'category__in' => array( $old_cat ),
				'post_type'    => 'post',
				'nopaging'     => 'true',
			) );

			foreach ( $posts as $post ) {
				$current_cats = array_diff( wp_get_post_categories( $post->ID ), array( $old_cat ) );

				if ( -1 !== $new_cat ) {
					if ( isset( $_POST['smbm_mc_overwrite'] ) && 'overwrite' == $_POST['smbm_mc_overwrite'] ) {
						// Remove old categories.
						$current_cats = array( $new_cat );
					} else {
						// Add to existing categories.
						$current_cats[] = $new_cat;
					}
				}

				if ( count( $current_cats ) == 0 ) {
					$current_cats = array( get_option( 'default_category' ) );
				}
				$current_cats = array_values( $current_cats );
				wp_update_post(array(
					'ID'            => $post->ID,
					'post_category' => $current_cats,
				) );
			}

			$bm->msg = sprintf( _n( 'Moved %d post from the selected category', 'Moved %d posts from the selected category' , count( $posts ), 'bulk-move' ), count( $posts ) );
		}
	}

	/**
	 * Render move by tag box.
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

		$tags = bm_get_tags_or_fail();

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
						<?php bm_render_tags_dropdown( 'smbm_mt_old_tag', $tags ); ?>
						==>
					</td>
					<td scope="row" >
						<?php bm_render_tags_dropdown( 'smbm_mt_new_tag', $tags, true ); ?>
					</td>
				</tr>

			</table>
			<p>
				<?php _e( 'If the post contains other tags, then', 'bulk-move' ); ?>
				<input type="radio" name="smbm_mt_overwrite" value="overwrite" checked><?php _e( 'Remove them', 'bulk-move' ); ?>
				<input type="radio" name="smbm_mt_overwrite" value="no-overwrite"><?php _e( "Don't remove them", 'bulk-move' ); ?>
			</p>
		</fieldset>
		<p class="submit">
			<button type="submit" name="bm_action" value="move_tags" class="button-primary"><?php _e( 'Bulk Move ', 'bulk-move' ); ?>&raquo;</button>
		</p>
		<!-- Tag end-->
		<?php
	}

	/**
	 * Move posts from one tag to another.
	 *
	 * @static
	 * @access public
	 *
	 * @since  1.2.0
	 */
	public static function move_tags() {

		if ( check_admin_referer( 'sm-bulk-move-posts', 'sm-bulk-move-posts-nonce' ) ) {

			do_action( 'bm_pre_request_handler' );

			$wp_query = new WP_Query();
			$bm       = BULK_MOVE();

			$old_tag = absint( $_POST['smbm_mt_old_tag'] );
			$new_tag = ( -1 === $_POST['smbm_mt_new_tag'] ) ? -1 : absint( $_POST['smbm_mt_new_tag'] );

			$posts = $wp_query->query( array(
				'tag__in'   => $old_tag,
				'post_type' => 'post',
				'nopaging'  => 'true',
			));

			foreach ( $posts as $post ) {
				$current_tags = wp_get_post_tags( $post->ID, array( 'fields' => 'ids' ) );
				$current_tags = array_diff( $current_tags, array( $old_tag ) );

				if ( -1 !== $new_tag ) {
					if ( isset( $_POST['smbm_mt_overwrite'] ) && 'overwrite' == $_POST['smbm_mt_overwrite'] ) {
						// Remove old tags.
						$current_tags = array( $new_tag );
					} else {
						// add to existing tags.
						$current_tags[] = $new_tag;
					}
				}

				$current_tags = array_values( $current_tags );
				wp_set_post_tags( $post->ID, $current_tags );
			}

			$bm->msg = sprintf( _n( 'Moved %d post from the selected tag', 'Moved %d posts from the selected tag', count( $posts ), 'bulk-move' ), count( $posts ) );
		}
	}

	/**
	 * Render move category by tag box.
	 *
	 * @since 1.2
	 * @static
	 * @access public
	 */
	public static function render_move_category_by_tag_box() {

		if ( Bulk_Move_Util::is_posts_box_hidden( Bulk_Move::BOX_CATEGORY_BY_TAG ) ) {
			printf( __( 'This section just got enabled. Kindly <a href = "%1$s">refresh</a> the page to fully enable it.', 'bulk-move' ), 'tools.php?page=' . Bulk_Move::POSTS_PAGE_SLUG );

			return;
		}

		$tags = bm_get_tags_or_fail();

		if ( empty( $tags ) ) {
			return;
		}
		?>

		<!-- Category by Tag Start-->
		<h4>
			<?php _e( 'On the left side, select the tag whose post you want to move. In the right side select the category to which you want the posts to be moved.', 'bulk-move' ); ?>
		</h4>

		<fieldset class="options">
			<table class="optiontable">
				<tr>
					<td scope="row">
						<?php bm_render_tags_dropdown( 'smbm_mct_old_tag', $tags ); ?>
						==>
					</td>
					<td scope="row" >
						<?php
						wp_dropdown_categories( array(
							'name'             => 'smbm_mct_mapped_cat',
							'show_count'       => true,
							'hierarchical'     => true,
							'orderby'          => 'NAME',
							'hide_empty'       => false,
							'show_option_none' => __( 'Choose Category', 'bulk-move' ),
						) );
						?>
					</td>
				</tr>

			</table>
			<p>
				<?php _e( 'If the post contains other categories, then', 'bulk-move' ); ?>
				<input type="radio" name="smbm_mct_overwrite" value="overwrite" checked><?php _e( 'Remove them', 'bulk-move' ); ?>
				<input type="radio" name="smbm_mct_overwrite" value="no-overwrite"><?php _e( "Don't remove them", 'bulk-move' ); ?>
			</p>
		</fieldset>
		<p class="submit">
			<button type="submit" name="bm_action" value="move_category_by_tag" class="button-primary"><?php _e( 'Bulk Move ', 'bulk-move' ); ?>&raquo;</button>
		</p>
		<!-- Tag end-->
		<?php
	}

	/**
	 * Move posts from a tag to another category.
	 *
	 * @static
	 * @access public
	 *
	 * @since  1.2.0
	 */
	public static function move_category_by_tag() {

		if ( check_admin_referer( 'sm-bulk-move-posts', 'sm-bulk-move-posts-nonce' ) ) {

			do_action( 'bm_pre_request_handler' );

			$wp_query = new WP_Query();
			$bm       = BULK_MOVE();

			$old_tag = absint( $_POST['smbm_mct_old_tag'] );
			$new_cat = ( -1 === $_POST['smbm_mct_mapped_cat'] ) ? -1 : absint( $_POST['smbm_mct_mapped_cat'] );

			$posts = $wp_query->query( array(
				'tag__in'   => $old_tag,
				'post_type' => 'post',
				'nopaging'  => 'true',
			));

			foreach ( $posts as $post ) {
				$current_cats = wp_get_post_categories( $post->ID );

				if ( -1 !== $new_cat ) {
					if ( isset( $_POST['smbm_mct_overwrite'] ) && 'overwrite' == $_POST['smbm_mct_overwrite'] ) {
						// Remove old categories.
						$current_cats = array( $new_cat );
					} else {
						// Add to existing categories.
						$current_cats[] = $new_cat;
					}
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

			$bm->msg = sprintf( _n( 'Moved %d post from the selected tag to the new category.', 'Moved %d posts from the selected tag to the new category.' , count( $posts ), 'bulk-move' ), count( $posts ) );
		}
	}

	/**
	 * Render debug box.
	 *
	 * @static
	 * @access public
	 *
	 * @since  1.0
	 */
	public static function render_debug_box() {

		// Get max script execution time from option.
		$max_execution_time = get_option( Bulk_Move::SCRIPT_TIMEOUT_OPTION );
		if ( ! $max_execution_time ) {
			$max_execution_time = '';
		}
		?>
		<!-- Debug box start-->
		<p>
			<?php _e( 'If you are seeing a blank page after clicking the Bulk Move button, then ', 'bulk-move' ); ?>
			<a href = "http://sudarmuthu.com/wordpress/bulk-move#faq"><?php _e( 'check out this FAQ', 'bulk-move' ); ?></a>.
			<?php _e( 'You also need need the following debug information.', 'bulk-move' ); ?>
		</p>
		<table cellspacing="10">
			<tr>
				<th align="right"><?php _e( 'PHP Version ', 'bulk-move' ); ?></th>
				<td><?php echo phpversion(); ?></td>
			</tr>
			<tr>
				<th align="right"><?php _e( 'WordPress Version ', 'bulk-move' ); ?></th>
				<td><?php echo get_bloginfo( 'version' ); ?></td>
			</tr>
			<tr>
				<th align="right"><?php _e( 'Plugin Version ', 'bulk-move' ); ?></th>
				<td><?php echo Bulk_Move::VERSION; ?></td>
			</tr>
			<tr>
				<th align="right"><?php _e( 'Available memory size ', 'bulk-move' ); ?></th>
				<td><?php echo ini_get( 'memory_limit' ); ?></td>
			</tr>
			<tr>
				<th align="right"><?php _e( 'Script time out ', 'bulk-move' ); ?></th>
				<td><strong><?php echo ini_get( 'max_execution_time' ); ?></strong> (<?php _e( 'In php.ini', 'bulk-move' ); ?>). <?php _e( 'Custom value: ', 'bulk-move' ); ?><input type="text" id="smbm_max_execution_time" name="smbm_max_execution_time" value="<?php echo $max_execution_time; ?>" > <button type="submit" name="bm_action" value="save_timeout" class="button-primary"><?php _e( 'Save', 'bulk-move' ) ?> &raquo;</button></td>
			</tr>
			<tr>
				<th align="right"><?php _e( 'Script input time ', 'bulk-move' ); ?></th>
				<td><?php echo ini_get( 'max_input_time' ); ?></td>
			</tr>
		</table>

		<p><em><?php _e( 'If you are looking to delete posts in bulk, try out my ', 'bulk-move' ); ?> <a href = "http://sudarmuthu.com/wordpress/bulk-delete"><?php _e( 'Bulk Delete Plugin', 'bulk-move' ); ?></a>.</em></p>
		<!-- Debug box end-->
		<?php
	}

	/**
	 * Save php timeout value.
	 *
	 * @static
	 * @access public
	 *
	 * @since  1.2.0
	 */
	public static function save_timeout() {

		if ( check_admin_referer( 'sm-bulk-move-posts', 'sm-bulk-move-posts-nonce' ) ) {
			$bm                     = BULK_MOVE();
			$new_max_execution_time = $_POST['smbm_max_execution_time'];

			if ( is_numeric( $new_max_execution_time ) ) {
				$option_updated = update_option( Bulk_Move::SCRIPT_TIMEOUT_OPTION, $new_max_execution_time );

				if ( $option_updated ) {
					$bm->msg = sprintf( __( 'Max execution time was successfully saved as %s seconds.', 'bulk-move' ), $new_max_execution_time );
				} else {
					// Error saving option.
					$bm->msg = __( 'An unknown error occurred while saving your options.', 'bulk-move' );
				}
			} else {
				// Error, value was not numeric.
				$bm->msg = sprintf( __( 'Could not update the max execution time to %s, it was not numeric.  Enter the max number of seconds this script should run.', 'bulk-move' ), $new_max_execution_time );
			}
		}
	}

	/**
	 * Change php `script_timeout`.
	 *
	 * @static
	 * @access public
	 *
	 * @since  1.2.0
	 */
	public static function change_timeout() {
		// get max script execution time from option.
		$max_execution_time = get_option( Bulk_Move::SCRIPT_TIMEOUT_OPTION );
		if ( ! $max_execution_time ) {
			// Increase script timeout in order to handle many posts.
			ini_set( 'max_execution_time', $max_execution_time );
		}
	}

	/**
	 * Loads the custom Taxonomy by Post Type.
	 *
	 * @since 1.3.0
	 */
	public static function load_custom_taxonomy_by_post_type() {
		check_ajax_referer( Bulk_Move::BOX_CUSTOM_TERMS_NONCE, 'nonce' );

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
	 * @since 1.3.0
	 */
	public static function load_custom_terms_by_taxonomy() {
		check_ajax_referer( Bulk_Move::BOX_CUSTOM_TERMS_NONCE, 'nonce' );

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
				$terms[ $wp_term->term_id ] = array( 'term_name' => esc_html( $wp_term->name ), 'term_count' => absint( $wp_term->count ) );
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
	 * Render move terms box.
	 *
	 * @since 1.3.0
	 */
	public static function render_move_by_custom_taxonomy_box() {

		if ( Bulk_Move_Util::is_posts_box_hidden( Bulk_Move::BOX_CATEGORY ) ) {
			printf( __( 'This section just got enabled. Kindly <a href = "%1$s">refresh</a> the page to fully enable it.', 'bulk-move' ), 'tools.php?page=' . Bulk_Move::POSTS_PAGE_SLUG );

			return;
		}
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
				<input type="radio" name="smbm_mbct_overwrite" value="overwrite" checked><?php _e( 'Remove them', 'bulk-move' ); ?>
				<input type="radio" name="smbm_mbct_overwrite" value="no-overwrite"><?php _e( "Don't remove them", 'bulk-move' ); ?>
			</p>

		</fieldset>

		<p class="submit bm_ct_submit">
			<button type="submit" name="bm_action" value="move_custom_taxonomy" class="button-primary"><?php _e( 'Bulk Move ', 'bulk-move' ); ?>&raquo;</button>
		</p>

		<!-- Custom Taxonomy end-->
		<?php
	}

	/**
	 * Move posts from one custom taxonomy to another.
	 *
	 * @since 1.3.0
	 */
	public static function move_custom_taxonomy() {
		if ( ! check_admin_referer( 'sm-bulk-move-posts', 'sm-bulk-move-posts-nonce' ) ) {
			return;
		}

		do_action( 'bm_pre_request_handler' );

		$wp_query = new WP_Query();
		$bm       = BULK_MOVE();

		$old_term   = absint( $_POST['smbm_mbct_selected_term'] );
		$taxonomy   = $_POST['smbm_mbct_taxonomy'];
		$post_types = array( $_POST['smbm_mbct_post_type'] );

		$new_term = ( -1 === $_POST['smbm_mbct_mapped_term'] ) ? -1 : absint( $_POST['smbm_mbct_mapped_term'] );

		$posts_count = 0 ;

		if ( -1 !== $old_term ) {
			foreach ( $post_types as $post_type ) {
				$posts_args = array(
					'tax_query' => array(
						array(
							'taxonomy' => $taxonomy,
							'field'    => 'term_id',
							'terms'    => $old_term,
						),
					),
					'post_type' => $post_type,
					'nopaging'  => 'true',
				);

				$posts = $wp_query->query( $posts_args );
				$posts_count += count( $posts );

				foreach ( $posts as $post ) {

					if ( -1 !== $new_term ) {
						if ( isset( $_POST['smbm_mbct_overwrite'] ) && 'overwrite' == $_POST['smbm_mbct_overwrite'] ) {
							$is_append_terms = false;
						} else {
							$is_append_terms = true;
						}
						wp_set_object_terms( $post->ID, $new_term, $taxonomy, $is_append_terms );
					} else {
						wp_remove_object_terms( $post->ID, $old_term, $taxonomy );
					}
				}
			}
		}

		/* translators: 1 number of posts deleted, 2 the taxonomy from which the posts were deleted */
		$bm->msg = sprintf( _n( 'Moved %1$d post from the selected %2$s taxonomy', 'Moved %1$d posts from the selected %2$s taxonomy', $posts_count, 'bulk-move' ), $posts_count, $taxonomy );
	}
}

add_action( 'bm_pre_request_handler'  , array( 'Bulk_Move_Posts', 'change_timeout' ) );
add_action( 'bm_move_cats'            , array( 'Bulk_Move_Posts', 'move_cats' ) );
add_action( 'bm_move_tags'            , array( 'Bulk_Move_Posts', 'move_tags' ) );
add_action( 'bm_move_category_by_tag' , array( 'Bulk_Move_Posts', 'move_category_by_tag' ) );
add_action( 'bm_save_timeout'         , array( 'Bulk_Move_Posts', 'save_timeout' ) );
add_action( 'bm_move_custom_taxonomy' , array( 'Bulk_Move_Posts', 'move_custom_taxonomy' ) );
add_action( 'wp_ajax_load_custom_taxonomy_by_post_type', array( 'Bulk_Move_Posts', 'load_custom_taxonomy_by_post_type' ) );
add_action( 'wp_ajax_load_custom_terms_by_taxonomy', array( 'Bulk_Move_Posts', 'load_custom_terms_by_taxonomy' ) );
