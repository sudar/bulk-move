<?php
/*
Plugin Name: Bulk Move
Plugin Script: bulk-move.php
Plugin URI: http://sudarmuthu.com/wordpress/bulk-move
Description: Bulk move posts from selected categories or tags.
Version: 0.9
Donate Link: http://sudarmuthu.com/if-you-wanna-thank-me
License: GPL
Author: Sudar
Author URI: http://sudarmuthu.com/

=== RELEASE NOTES ===
2009-02-04 - v0.1 - first version
2009-05-08 - v0.2 - first version
2010-11-28 - v0.3 - Fixes for blank screen issue. Thanks Carlos
2011-02-08 - v0.4 - Added Brazilian Portuguese translation
2011-08-25 - v0.5 - Fixed a warning and added Turkish translation
2011-11-19 - v0.6 - Added Spanish translation
2011-12-16 - v0.7 - Removed spaces from first line which was starting the output 
2012-01-13 - v0.8 - Added Bulgarian translations
2012-07-10 - v0.6 - (Dev time: 0.5 hour)
                  - Added Hindi translations

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

/**
 * Request Handler
 */
if (!function_exists('smbm_request_handler')) {
    function smbm_request_handler() {
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
            add_action( 'admin_notices', 'smbm_moved_notice', 9 );
        }
    }
}

/**
 * Show moved notice messages
 */
function smbm_moved_notice() {
    echo "<div class = 'updated'><p>" . __("All the selected posts have been sucessfully moved.", 'bulk-move') ."</p></div>";
}

/**
 * Show the Admin page
 */
if (!function_exists('smbm_displayOptions')) {
    function smbm_displayOptions() {
        global $wpdb;
?>
	<div class="updated fade" style="background:#ff0;text-align:center;color: red;"><p><strong><?php _e("WARNING: Posts moved once cannot be undone. Use with caution.", 'bulk-move'); ?></strong></p></div>
    <div class="wrap">
		<h2>Bulk Move</h2>

        <h3><?php _e("By Category", 'bulk-move'); ?></h3>
        <h4><?php _e("On the left side, select the category whose post you want to move. In the right side select the category to which you want the posts to be moved.", 'bulk-move') ?></h4>

        <form name="smbm_form" id = "smbm_cat_form"
        action="<?php echo get_bloginfo("wpurl"); ?>/wp-admin/options-general.php?page=bulk-move.php" method="post"
        onsubmit="return bd_validateForm(this);">

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
				<input type="submit" name="submit" value="<?php _e("Bulk Move ", 'bulk-move') ?>&raquo;">
        </p>

<?php wp_nonce_field('bulk-move-cats'); ?>

		<input type="hidden" name="smbm_action" value="bulk-move-cats" />
		</form>
        <p><em><?php _e("If you are looking to delete posts in bulk, try out my ", 'bulk-move'); ?> <a href = "http://sudarmuthu.com/wordpress/bulk-delete"><?php _e("Bulk Delete Plugin", 'bulk-move');?></a>.</em></p>
    </div>
<?php

    // Display credits in Footer
    add_action( 'in_admin_footer', 'smbm_admin_footer' );
    }
}

/**
 * Print JavaScript
 */
function smbm_print_scripts() {
?>
<script type="text/javascript">

    /**
     * Validate Form
     */
    function bd_validateForm(form) {
        return true;
        var valid = false;
        for (i = 0, n = form.elements.length; i < n; i++) {
            if(form.elements[i].type == "checkbox" && !(form.elements[i].getAttribute('onclick',2))) {
                if(form.elements[i].checked == true) {
                    valid = true;
                    break;
                }
            }
        }

        if (valid) {
            return confirm("<?php _e('Are you sure you want to move all the selected posts', 'bulk-move'); ?>");
        } else {
            alert ("<?php _e('Please select at least one', 'bulk-move'); ?>");
            return false;
        }
    }
</script>
<?php
}

/**
 * Add navigation menu
 */
if(!function_exists('smbm_add_menu')) {
	function smbm_add_menu() {
	    //Add a submenu to Manage
        add_options_page("Bulk Move", "Bulk Move", 8, basename(__FILE__), "smbm_displayOptions");
	}
}

/**
 * Adds the settings link in the Plugin page. Based on http://striderweb.com/nerdaphernalia/2008/06/wp-use-action-links/
 * @staticvar <type> $this_plugin
 * @param <type> $links
 * @param <type> $file
 */
function smbm_filter_plugin_actions($links, $file) {
    static $this_plugin;
    if( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

    if( $file == $this_plugin ) {
        $settings_link = '<a href="options-general.php?page=bulk-move.php">' . __('Manage', 'bulk-move') . '</a>';
        array_unshift( $links, $settings_link ); // before other links
    }
    return $links;
}

/**
 * Adds Footer links. Based on http://striderweb.com/nerdaphernalia/2008/06/give-your-wordpress-plugin-credit/
 */
function smbm_admin_footer() {
	$plugin_data = get_plugin_data( __FILE__ );
    printf('%1$s ' . __("plugin", 'bulk-move') .' | ' . __("Version", 'bulk-move') . ' %2$s | '. __('by', 'bulk-move') . ' %3$s<br />', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author']);
}

add_filter( 'plugin_action_links', 'smbm_filter_plugin_actions', 10, 2 );

add_action('admin_menu', 'smbm_add_menu');
add_action('init', 'smbm_request_handler');
add_action('admin_head', 'smbm_print_scripts');
?>
