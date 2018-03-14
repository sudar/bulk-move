<?php
/**
 * Uninstall file for Bulk Move plugin.
 *
 * @since 1.2
 *
 * @author Sudar
 */

//if uninstall not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

$option_name = 'bm_max_execution_time';

if ( ! is_multisite() ) {
	// For Single site
	delete_option( $option_name );
} else {
	// For Multisite
	global $wpdb;

	// For regular options.
	$blog_ids         = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	$original_blog_id = get_current_blog_id();

	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );
		delete_option( $option_name );
	}
	switch_to_blog( $original_blog_id );

	// For site options.
	delete_site_option( $option_name );
}
