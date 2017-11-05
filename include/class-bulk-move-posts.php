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
        <h4><?php _e( 'On the left side, select the category whose post you want to move. In the right side select the category to which you want the posts to be moved.', 'bulk-move' ) ?></h4>

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
            <button type="submit" name="bm_action" value="move_cats" class="button-primary"><?php _e( 'Bulk Move ', 'bulk-move' ) ?>&raquo;</button>
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

            $wp_query = new WP_Query;
            $bm       = BULK_MOVE();

            // move by cats
            $old_cat = absint( $_POST['smbm_mc_selected_cat'] );
            $new_cat = ( $_POST['smbm_mc_mapped_cat'] == -1 ) ? -1 : absint( $_POST['smbm_mc_mapped_cat'] );

            $posts = $wp_query->query(array(
                'category__in' => array( $old_cat ),
                'post_type'    => 'post',
                'nopaging'     => 'true',
            ) );

            foreach ( $posts as $post ) {
                $current_cats = array_diff( wp_get_post_categories( $post->ID ), array( $old_cat ) );

                if ( $new_cat != -1 ) {
                    if ( isset( $_POST['smbm_mc_overwrite'] ) && 'overwrite' == $_POST['smbm_mc_overwrite'] ) {
                        // Remove old categories
                        $current_cats = array( $new_cat );
                    } else {
                        // Add to existing categories
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
?>
        <!-- Tag Start-->
        <h4><?php _e( 'On the left side, select the tag whose post you want to move. In the right side select the tag to which you want the posts to be moved.', 'bulk-move' ) ?></h4>

        <fieldset class="options">
		<table class="optiontable">
            <tr>
                <td scope="row" >
                <select name="smbm_mt_old_tag">
<?php
                $tags = get_tags( array( 'hide_empty' => false ) );
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
                <select name="smbm_mt_new_tag">
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
        <p>
            <?php _e( 'If the post contains other tags, then', 'bulk-move' ); ?>
            <input type="radio" name="smbm_mt_overwrite" value="overwrite" checked><?php _e( 'Remove them', 'bulk-move' ); ?>
            <input type="radio" name="smbm_mt_overwrite" value="no-overwrite"><?php _e( "Don't remove them", 'bulk-move' ); ?>
        </p>
		</fieldset>
        <p class="submit">
            <button type="submit" name="bm_action" value="move_tags" class="button-primary"><?php _e( 'Bulk Move ', 'bulk-move' ) ?>&raquo;</button>
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

            $wp_query = new WP_Query;
            $bm       = BULK_MOVE();

            // move by tags
            $old_tag = absint( $_POST['smbm_mt_old_tag'] );
            $new_tag = ( $_POST['smbm_mt_new_tag'] == -1 ) ? -1 : absint( $_POST['smbm_mt_new_tag'] );

            $posts = $wp_query->query( array(
                'tag__in'   => $old_tag,
                'post_type' => 'post',
                'nopaging'  => 'true',
            ));

            foreach ( $posts as $post ) {
                $current_tags = wp_get_post_tags( $post->ID, array( 'fields' => 'ids' ) );
                $current_tags = array_diff( $current_tags, array( $old_tag ) );

                if ( $new_tag != -1 ) {
                    if ( isset( $_POST['smbm_mt_overwrite'] ) && 'overwrite' == $_POST['smbm_mt_overwrite'] ) {
                        // Remove old tags
                        $current_tags = array( $new_tag );
                    } else {
                        // add to existing tags
                        $current_tags[] = $new_tag;
                    }
                }

                $current_tags = array_values( $current_tags );
                wp_set_post_tags( $post->ID, $current_tags );
            }

            $bm->msg = sprintf( _n( 'Moved %d post from the selected tag', 'Moved %d posts from the selected tag' , count( $posts ), 'bulk-move' ), count( $posts ) );
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
        ?>
        <!-- Tag Start-->
        <h4><?php _e( 'On the left side, select the tag whose post you want to move. In the right side select the category to which you want the posts to be moved.', 'bulk-move' ) ?></h4>

        <fieldset class="options">
            <table class="optiontable">
                <tr>
                    <td scope="row" >
                        <select name="smbm_mct_old_tag">
<?php
                            $tags = get_tags( array( 'hide_empty' => false ) );
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
            <button type="submit" name="bm_action" value="move_category_by_tag" class="button-primary"><?php _e( 'Bulk Move ', 'bulk-move' ) ?>&raquo;</button>
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

            $wp_query = new WP_Query;
            $bm       = BULK_MOVE();

            // move by tags
            $old_tag = absint( $_POST['smbm_mct_old_tag'] );
            $new_cat = ( $_POST['smbm_mct_mapped_cat'] == -1 ) ? -1 : absint( $_POST['smbm_mct_mapped_cat'] );

            $posts = $wp_query->query( array(
                'tag__in'   => $old_tag,
                'post_type' => 'post',
                'nopaging'  => 'true',
            ));

            foreach ( $posts as $post ) {
                $current_cats = wp_get_post_categories( $post->ID );

                if ( $new_cat != -1 ) {
                    if ( isset( $_POST['smbm_mct_overwrite'] ) && 'overwrite' == $_POST['smbm_mct_overwrite'] ) {
                        // Remove old categories
                        $current_cats = array( $new_cat );
                    } else {
                        // Add to existing categories
                        $current_cats[] = $new_cat;
                    }
                }

                if ( count( $current_cats ) == 0) {
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
                <a href = "http://sudarmuthu.com/wordpress/bulk-move#faq"><?php _e( 'check out this FAQ', 'bulk-move' );?></a>.
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
                <th align="right"><?php _e( 'Available memory size ', 'bulk-move' );?></th>
                <td><?php echo ini_get( 'memory_limit' ); ?></td>
            </tr>
            <tr>
                <th align="right"><?php _e( 'Script time out ', 'bulk-move' );?></th>
                <td><strong><?php echo ini_get( 'max_execution_time' );?></strong> (<?php _e( 'In php.ini', 'bulk-move' );?>). <?php _e( 'Custom value: ', 'bulk-move' );?><input type="text" id="smbm_max_execution_time" name="smbm_max_execution_time" value="<?php echo $max_execution_time; ?>" > <button type="submit" name="bm_action" value="save_timeout" class="button-primary"><?php _e( 'Save', 'bulk-move' ) ?> &raquo;</button></td>
            </tr>
            <tr>
                <th align="right"><?php _e( 'Script input time ', 'bulk-move' ); ?></th>
                <td><?php echo ini_get( 'max_input_time' ); ?></td>
            </tr>
        </table>

        <p><em><?php _e( 'If you are looking to delete posts in bulk, try out my ', 'bulk-move' ); ?> <a href = "http://sudarmuthu.com/wordpress/bulk-delete"><?php _e( 'Bulk Delete Plugin', 'bulk-move' );?></a>.</em></p>
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

            if (is_numeric( $new_max_execution_time ) ) {
                //Update option.
                $option_updated = update_option( Bulk_Move::SCRIPT_TIMEOUT_OPTION, $new_max_execution_time );

                if ( $option_updated === true ) {
                    //Success.
                    $bm->msg = sprintf( __( 'Max execution time was successfully saved as %s seconds.', 'bulk-move' ), $new_max_execution_time );
                } else {
                    //Error saving option.
                    $bm->msg = __( 'An unknown error occurred while saving your options.', 'bulk-move' );
                }
            } else {
                //Error, value was not numeric.
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
            //Increase script timeout in order to handle many posts.
            ini_set( 'max_execution_time', $max_execution_time );
        }
    }

	/**
	 * Loads the custom Taxonomy by Post Type.
	 *
	 * @since 1.3.0
	 */
	public static function load_custom_taxonomy_by_post_type() {
		$bulk_move = BULK_MOVE();
		check_ajax_referer( $bulk_move::BOX_CUSTOM_TERMS_NONCE, 'security' );
		$post_type = isset( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : 'post';

		$taxonomies = get_object_taxonomies( $post_type );

		$args = array(
			'taxonomy'   => $taxonomies,
			'hide_empty' => false,
			'orderby'    => 'name',
		);

		ob_start();
        ?>
		<option class="level-0" value="select"><?php _e( 'Select Taxonomy&nbsp;&nbsp;', 'bulk-move' ); ?></option>
        <?php
		foreach ( $taxonomies as $taxonomy ) :
			?>
			<option class="level-0" value="<?php echo $taxonomy; ?>"><?php echo $taxonomy; ?></option>
			<?php
		endforeach;
		$data = ob_get_clean();
		wp_send_json_success( $data );
	}

	/**
	 * Loads the custom Terms by Taxonomy.
	 *
	 * @since 1.3.0
	 */
	public static function load_custom_terms_by_taxonomy() {
		$bulk_move = BULK_MOVE();
		check_ajax_referer( $bulk_move::BOX_CUSTOM_TERMS_NONCE, 'security' );
		$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_text_field( $_POST['taxonomy'] ) : 'category';

		$args = array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'orderby'    => 'name',
		);
		$terms = get_terms( $args );

		$select_term = '<option class="level-0" value="-1">' . __( 'Select Term&nbsp;&nbsp;', '"bulk-move' ) . '</option>';
		$map_term    = '<option class="level-0" value="-1">' . __( 'Remove Term&nbsp;&nbsp;', 'bullk-move' ) . '</option>';
		ob_start();
		foreach ( $terms as $term ) :
		?>
			<option class="level-0" value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?>&nbsp;&nbsp;(<?php echo $term->count; ?>)</option>
		<?php
		endforeach;
		$ob = ob_get_clean();
		$select_term .= $ob;
		$map_term .= $ob;
		$data = array( 'select_term' => $select_term, 'map_term' => $map_term );
		wp_send_json_success( $data );
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
		<h4><?php _e( 'Select the post type to show its taxonomy. On the left side, select the term whose posts you want to move. On the right side select the term to which you want the posts to be moved.', 'bulk-move' ) ?></h4>

		<fieldset class="options">
			<table class="optiontable">
				<tr>
					<td scope="row" colspan="2">
						<?php _e( 'Select the post type to show its custom taxonomy.', 'bulk-move' ) ?>
					</td>
					<td scope="row">
				</tr>
				<tr>
					<td scope="row" colspan="2">
					<?php
						$custom_post_types_args = array( '_builtin' => false );
						$custom_post_types      = get_post_types( $custom_post_types_args );
					?>
					<?php if ( count( $custom_post_types ) === 0 ) : ?>
						<p>
							<span class="error-notice"><?php _e( 'You have no custom post type registered.', 'bulk-move' ); ?></span>
						</p>
					<?php endif; ?>
						<p>
							<select name="smbm_mbct_post_type" id="smbm_mbct_post_type">
								<option value="select"><?php _e( 'Select Post type', 'bulk-move' ); ?></option>
							<?php foreach ( $custom_post_types as $post_type ) : ?>
								<option value="<?php echo $post_type; ?>"><?php echo $post_type; ?></option>
							<?php endforeach; ?>
							</select>
						</p>
					</td>
				</tr>
				<tr class="taxonomy-select-row">
					<td scope="row" colspan="2">
						<?php _e( 'Select taxonomy to show its terms.', 'bulk-move' ) ?>
					</td>
					<td scope="row">
				</tr>
				<tr class="taxonomy-select-row">
					<td scope="row" colspan="2">
						<p>
							<select name="smbm_mbct_taxonomy" id="smbm_mbct_taxonomy">
								<option value="select"><?php _e( 'Select Taxonomy', 'bulk-move' ); ?></option>
							</select>
						</p>
					</td>
				</tr>
				<tr class="term-select-row">
					<td scope="row" colspan="2">
						<?php _e( 'Select terms to move its posts.', 'bulk-move' ) ?>
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
			<p>
				<?php _e( 'If the post contains other terms, then', 'bulk-move' ); ?>
				<input type="radio" name="smbm_mbct_overwrite" value="overwrite" checked><?php _e( 'Remove them', 'bulk-move' ); ?>
				<input type="radio" name="smbm_mbct_overwrite" value="no-overwrite"><?php _e( "Don't remove them", 'bulk-move' ); ?>
			</p>

		</fieldset>
		<p class="submit">
			<button type="submit" name="bm_action" value="move_custom_taxonomy" class="button-primary"><?php _e( 'Bulk Move ', 'bulk-move' ) ?>&raquo;</button>
		</p>
		<!-- Custom Taxonomy end-->
		<?php
	}

	public static function move_custom_taxonomy() {
		if ( check_admin_referer( 'sm-bulk-move-posts', 'sm-bulk-move-posts-nonce' ) ) {

			do_action( 'bm_pre_request_handler' );

			$wp_query = new WP_Query;
			$bm       = BULK_MOVE();

			// Move by terms.
			$old_cat    = absint( $_POST['smbm_mbct_selected_term'] );
			$taxonomy   = $_POST['smbm_mbct_taxonomy'];
			$post_types = array( $_POST['smbm_mbct_post_type'] );

			$new_cat = ( $_POST['smbm_mbct_mapped_term'] == -1 ) ? -1 : absint( $_POST['smbm_mbct_mapped_term'] );

			$posts_count = 0 ;

			if ( $old_cat !== -1 ) {
				foreach ( $post_types as $post_type ) {
					$posts_args = array(
						'tax_query' => array(
							array(
								'taxonomy' => $taxonomy,
								'field'    => 'term_id',
								'terms'    => $old_cat,
							),
						),
						'post_type' => $post_type,
						'nopaging'  => 'true',
					);

					$posts = $wp_query->query( $posts_args );
					$posts_count += count( $posts );

					foreach ( $posts as $post ) {

						if ( $new_cat != -1 ) {
							if ( isset( $_POST['smbm_mbct_overwrite'] ) && 'overwrite' == $_POST['smbm_mbct_overwrite'] ) {
								// Remove old categories
								$is_append_terms = false;
							} else {
								// Add to existing categories
								$is_append_terms = true;
							}
							wp_set_object_terms( $post->ID, $new_cat, $taxonomy, $is_append_terms );
						} else {
							wp_remove_object_terms( $post->ID, $old_cat, $taxonomy );
						}
					}
				}
			}

			$bm->msg = sprintf( _n( 'Moved %d post from the selected category', 'Moved %d posts from the selected category' , $posts_count, 'bulk-move' ), $posts_count );
		}
	}
}

// Hooks
add_action( 'bm_pre_request_handler'  , array( 'Bulk_Move_Posts', 'change_timeout' ) );
add_action( 'bm_move_cats'            , array( 'Bulk_Move_Posts', 'move_cats' ) );
add_action( 'bm_move_tags'            , array( 'Bulk_Move_Posts', 'move_tags' ) );
add_action( 'bm_move_category_by_tag' , array( 'Bulk_Move_Posts', 'move_category_by_tag' ) );
add_action( 'bm_save_timeout'         , array( 'Bulk_Move_Posts', 'save_timeout' ) );
add_action( 'bm_move_custom_taxonomy' , array( 'Bulk_Move_Posts', 'move_custom_taxonomy' ) );
?>
