<?php
/*
Plugin Name: Bulk Move
Plugin Script: bulk-move.php
Plugin URI: http://sudarmuthu.com/wordpress/bulk-move
Description: Bulk move posts from selected categories or tags.
Version: 1.0
Donate Link: http://sudarmuthu.com/if-you-wanna-thank-me
License: GPL
Author: Sudar
Author URI: http://sudarmuthu.com/

=== RELEASE NOTES ===
Checkout readme file for release notes

/*  Copyright 2009  Sudar Muthu  (email : sudar@sudarmuthu.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Bulk_Move {
    const VERSION               = '1.0';

    // page slugs
    const POSTS_PAGE_SLUG       = 'bulk-move-posts';

    // JS constants
    const JS_HANDLE             = 'bulk-move';
    const JS_VARIABLE           = 'BULK_MOVE';

    /**
     * Default constructor
     */
    public function __construct() {
        // Load localization domain
        $this->translations = dirname( plugin_basename( __FILE__ ) ) . '/languages/' ;
        load_plugin_textdomain( 'bulk-move', FALSE, $this->translations );

        // Register hooks
        add_action( 'admin_menu', array( &$this, 'add_menu' ) );
        add_action( 'admin_init', array( &$this, 'request_handler' ) );

        // Add more links in the plugin listing page
        add_filter( 'plugin_action_links', array( &$this, 'filter_plugin_actions' ), 10, 2 );
    }

    /**
     * Add navigation menu
     */
	function add_menu() {

        $this->post_page = add_submenu_page( 'tools.php', __( 'Bulk Move' , 'bulk-move'), __( 'Bulk Move' , 'bulk-move'), 'edit_posts', self::POSTS_PAGE_SLUG, array( &$this, 'display_posts_page' ) );

        // enqueue JavaScript
        add_action( 'admin_print_scripts-' . $this->post_page, array( &$this, 'add_script') );
	}

    /**
     * Show the Admin page
     */
    function display_posts_page() {
?>
    <div class="wrap">
        <?php screen_icon(); ?>
        <h2><?php _e( 'Bulk Move Posts', 'bulk-move' );?></h2>

        <div id="post-body-content">
            <div class="updated" >
                <p><strong><?php _e( 'WARNING: Posts moved once cannot be undone. Use with caution.', 'bulk-move' ); ?></strong></p>
            </div>
        </div><!-- #post-body-content -->

        <form method = "post">

        <h3><?php _e('By Category', 'bulk-move'); ?></h3>
        <h4><?php _e('On the left side, select the category whose post you want to move. In the right side select the category to which you want the posts to be moved.', 'bulk-move') ?></h4>

        <fieldset class="options">
		<table class="optiontable">
            <tr>
                <td scope="row" >
                <select name="smbm_selected_cat">
<?php
        $categories =  get_categories(array('hide_empty' => false));
        foreach ($categories as $category) {
?>
                    <option value="<?php echo $category->cat_ID; ?>">
                    <?php echo $category->cat_name; ?> (<?php echo $category->count . " "; _e("Posts", 'bulk-move'); ?>)
                    </option>
<?php
        }
?>
                </select>
                ==>
                </td>
                <td scope="row" >
                <select name="smbm_mapped_cat">
                <option value="-1"><?php _e("Remove Category", 'bulk-move'); ?></option>
<?php
        foreach ($categories as $category) {
?>
                    <option value="<?php echo $category->cat_ID; ?>">
                    <?php echo $category->cat_name; ?> (<?php echo $category->count . " "; _e("Posts", 'bulk-move'); ?>)
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

<?php wp_nonce_field('bulk-move-cats'); ?>

		</form>
        <p><em><?php _e("If you are looking to delete posts in bulk, try out my ", 'bulk-move'); ?> <a href = "http://sudarmuthu.com/wordpress/bulk-delete"><?php _e("Bulk Delete Plugin", 'bulk-move');?></a>.</em></p>
    </div>
<?php
        // Display credits in Footer
        add_action( 'in_admin_footer', array( &$this, 'admin_footer' ) );
    }

    /**
    * Adds Footer links. Based on http://striderweb.com/nerdaphernalia/2008/06/give-your-wordpress-plugin-credit/
    */
    function admin_footer() {
        $plugin_data = get_plugin_data( __FILE__ );
        printf( '%1$s ' . __( 'plugin', 'bulk-move' ) .' | ' . __( 'Version', 'bulk-move' ) . ' %2$s | '. __( 'by', 'bulk-move' ) . ' %3$s<br />', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author'] );
    }

    /**
     * Enqueue JavaScript
     */
    function add_script() {
        global $wp_scripts;

        wp_enqueue_script( self::JS_HANDLE, plugins_url( '/js/bulk-move.js', __FILE__ ), array( 'jquery' ), self::VERSION, TRUE );

        // JavaScript messages
        $msg = array(
            'move_warning'      => __( 'Are you sure you want to move all the selected posts', 'bulk-move' )
        );

        $error = array(
            'select_one'    => __( 'Please select least one option', 'bulk-move' ),
        );

        $translation_array = array( 'msg' => $msg, 'error' => $error );
        wp_localize_script( self::JS_HANDLE, self::JS_VARIABLE, $translation_array );
    }

    /**
     * Request Handler
     */
    function request_handler() {
        if (isset($_POST['smbm_action'])) {

            $my_query = new WP_Query;
            check_admin_referer( 'bulk-move-cats');

            switch($_POST['smbm_action']) {

                case "bulk-move-cats":
                    // move by cats
                    $old_cat = absint($_POST['smbm_selected_cat']);
                    $new_cat   = ($_POST['smbm_mapped_cat'] == -1) ? -1 : absint($_POST['smbm_mapped_cat']);
                    $posts = $my_query->query(array('category__in'=>array($old_cat), 'post_type'=>'post', 'nopaging'=>'true'));
                    foreach ($posts as $post) {
                        
                        $current_cats = wp_get_post_categories($post->ID);
                        $current_cats = array_diff($current_cats, array($old_cat));
                        if ($new_cat != -1) {
                            $current_cats[] = $new_cat;
                        }

                        if (count($current_cats) == 0) {
                            $current_cats = array(get_option('default_category'));
                        }
                        $current_cats = array_values($current_cats);
                        wp_update_post(array('ID'=>$post->ID,'post_category'=>$current_cats));
                    }

                    $this->msg = sprintf( _n( 'Moved %d post from the selected category', 'Moved %d posts from the selected category' , count( $posts ), 'bulk-move' ), count( $posts ) );

                    break;

                case "bulk-move-tags":
                    // move by tags
                    $selected_tags = $_POST['smbm_tags'];
                    $posts = $my_query->query(array('tag__in'=>$selected_tags, 'post_type'=>'post', 'nopaging'=>'true'));

                    foreach ($posts as $post) {
                        wp_move_post($post->ID);
                    }
                    
                    break;
            }

            // hook the admin notices action
            add_action( 'admin_notices', array( &$this, 'moved_notice' ), 9 );
        }
    }

    /**
     * Show moved notice messages
     */
    function moved_notice() {
        if ( isset( $this->msg ) && $this->msg != '' ) {
            echo "<div class = 'updated'><p>" . $this->msg . "</p></div>";
        }

        // cleanup
        $this->msg = '';
        remove_action( 'admin_notices', array( &$this, 'moved_notice' ) );
    }

    /**
     * Adds the settings link in the Plugin page. Based on http://striderweb.com/nerdaphernalia/2008/06/wp-use-action-links/
     * @staticvar <type> $this_plugin
     * @param <type> $links
     * @param <type> $file
     */
    function filter_plugin_actions( $links, $file ) {
        static $this_plugin;
        if( ! $this_plugin ) $this_plugin = plugin_basename( __FILE__ );

        if( $file == $this_plugin ) {
            $settings_link = '<a href="tools.php?page=' . self::POSTS_PAGE_SLUG . '">' . __( 'Manage', 'bulk-move' ) . '</a>';
            array_unshift( $links, $settings_link ); // before other links
        }
        return $links;
    }
}

// Start this plugin once all other plugins are fully loaded
add_action( 'init', 'Bulk_Move' ); function Bulk_Move() { global $Bulk_Move; $Bulk_Move = new Bulk_Move(); }
?>
