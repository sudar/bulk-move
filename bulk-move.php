<?php
/**
Plugin Name: Bulk Move
Plugin Script: bulk-move.php
Plugin URI: http://sudarmuthu.com/wordpress/bulk-move
Description: Move or remove posts in bulk from one category or tag to another
Version: 1.1
Donate Link: http://sudarmuthu.com/if-you-wanna-thank-me
License: GPL
Author: Sudar
Author URI: http://sudarmuthu.com/
Text Domain: bulk-move
Domain Path: languages/

=== RELEASE NOTES ===
Checkout readme file for release notes
*/

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

if ( !class_exists( 'Bulk_Move_Posts' ) ) {
    require_once dirname( __FILE__ ) . '/include/class-bulk-move-posts.php';
}

if ( !class_exists( 'Bulk_Move_Util' ) ) {
    require_once dirname( __FILE__ ) . '/include/class-bulk-move-util.php';
}

class Bulk_Move {
    const VERSION               = '1.1';

    // page slugs
    const POSTS_PAGE_SLUG       = 'bulk-move-posts';

    // JS constants
    const JS_HANDLE             = 'bulk-move';
    const JS_VARIABLE           = 'BULK_MOVE';

    // meta boxes for delete posts
    const BOX_CATEGORY          = 'bm_move_category';
    const BOX_TAG               = 'bm_move_tag';
    const BOX_DEBUG             = 'bm_debug';

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

        // meta boxes
		add_action( "load-{$this->post_page}", array( &$this, 'add_move_posts_settings_panel' ) );
        add_action( "add_meta_boxes_{$this->post_page}", array( &$this, 'add_move_posts_meta_boxes' ) );
	}

    /**
     * Add settings Panel for move posts page
     *
     * @since 1.0
     */ 
	function add_move_posts_settings_panel() {
 
		/** 
		 * Create the WP_Screen object using page handle
		 */
		$this->move_posts_screen = WP_Screen::get( $this->post_page );
 
		/**
		 * Content specified inline
		 */
		$this->move_posts_screen->add_help_tab(
			array(
				'title'    => __( 'About Plugin', 'bulk-move' ),
				'id'       => 'about_tab',
				'content'  => '<p>' . __( 'This plugin allows you to move posts in bulk from selected categories to another category', 'bulk-move' ) . '</p>',
				'callback' => false
			)
		);
 
        // Add help sidebar
		$this->move_posts_screen->set_help_sidebar(
            '<p><strong>' . __( 'More information', 'bulk-move' ) . '</strong></p>' .
            '<p><a href = "http://sudarmuthu.com/wordpress/bulk-move">' . __( 'Plugin Homepage/support', 'bulk-move' ) . '</a></p>' .
            '<p><a href = "http://sudarmuthu.com/blog">' . __( "Plugin author's blog", 'bulk-move' ) . '</a></p>' .
            '<p><a href = "http://sudarmuthu.com/wordpress/">' . __( "Other Plugin's by Author", 'bulk-move' ) . '</a></p>'
        );

        /* Trigger the add_meta_boxes hooks to allow meta boxes to be added */
        do_action( 'add_meta_boxes_' . $this->post_page, null );
        do_action( 'add_meta_boxes', $this->post_page, null );
    
        /* Enqueue WordPress' script for handling the meta boxes */
        wp_enqueue_script( 'postbox' );
	}

    /**
     * Register meta boxes for move posts page
     *
     * @since 1.0
     */
    function add_move_posts_meta_boxes() {
        add_meta_box( self::BOX_CATEGORY, __( 'Bulk Move By Category', 'bulk-move' ), 'Bulk_Move_Posts::render_move_category_box', $this->post_page, 'advanced' );
        add_meta_box( self::BOX_TAG, __( 'Bulk Move By Tag', 'bulk-move' ), 'Bulk_Move_Posts::render_move_tag_box', $this->post_page, 'advanced' );
        add_meta_box( self::BOX_DEBUG, __( 'Debug Information', 'bulk-move' ), 'Bulk_Move_Posts::render_debug_box', $this->post_page, 'advanced', 'low' );
    }

    /**
     * Show the Admin page
     */
    function display_posts_page() {
?>
<div class="wrap">
    <h2><?php _e( 'Bulk Move Posts', 'bulk-move' );?></h2>

    <form method = "post">
<?php
        // nonce for bulk move
        wp_nonce_field( 'sm-bulk-move-posts', 'sm-bulk-move-posts-nonce' );

        /* Used to save closed meta boxes and their order */
        wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
        wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
?>
    <div id = "poststuff">
        <div id="post-body" class="metabox-holder columns-2">

            <div id="post-body-content">
                <div class="updated" >
                    <p><strong><?php _e( 'WARNING: Posts moved once cannot be retrieved back. Use with caution.', 'bulk-move' ); ?></strong></p>
                </div>
            </div><!-- #post-body-content -->

            <div id="postbox-container-1" class="postbox-container">
                <iframe frameBorder="0" height = "1000" src = "http://sudarmuthu.com/projects/wordpress/bulk-move/sidebar.php?color=<?php echo get_user_option( 'admin_color' ); ?>&version=<?php echo self::VERSION; ?>"></iframe>
            </div>

            <div id="postbox-container-2" class="postbox-container">
                <?php do_meta_boxes( '', 'advanced', null ); ?>
            </div> <!-- #postbox-container-2 -->

        </div> <!-- #post-body -->
    </div><!-- #poststuff -->

    </form>
</div><!-- .wrap -->
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

            wp_nonce_field( 'sm-bulk-move-posts', 'sm-bulk-move-posts-nonce' );
            $my_query = new WP_Query;

            switch($_POST['smbm_action']) {

                case "bulk-move-cats":
                    // move by cats
                    $old_cat = absint($_POST['smbm_selected_cat']);
                    $new_cat = ($_POST['smbm_mapped_cat'] == -1) ? -1 : absint($_POST['smbm_mapped_cat']);
                    $posts   = $my_query->query(array('category__in'=>array($old_cat), 'post_type'=>'post', 'nopaging'=>'true'));

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
                    $old_tag = absint( $_POST['smbm_old_tag'] );
                    $new_tag = ( $_POST['smbm_new_tag'] == -1 ) ? -1 : absint( $_POST['smbm_new_tag'] );

                    $posts = $my_query->query( array( 
                        'tag__in'   => $old_tag,
                        'post_type' => 'post',
                        'nopaging'  => 'true'
                    ));

                    foreach ( $posts as $post ) {
                        $current_tags = wp_get_post_tags( $post->ID, array( 'fields' => 'ids' ) );
                        $current_tags = array_diff( $current_tags, array( $old_tag ) );

                        if ( $new_tag != -1 ) {
                            $current_tags[] = $new_tag;
                        }

                        $current_tags = array_values( $current_tags );
                        wp_set_post_tags( $post->ID, $current_tags );
                    }

                    $this->msg = sprintf( _n( 'Moved %d post from the selected tag', 'Moved %d posts from the selected tag' , count( $posts ), 'bulk-move' ), count( $posts ) );
                    
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
