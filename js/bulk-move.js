/**
 * JavaScript for Bulk move Plugin
 *
 * http://sudarmuthu.com/wordpress/bulk-move
 *
 * @author: Sudar <http://sudarmuthu.com>
 * 
 */

/*jslint browser: true, devel: true*/
/*global BULK_MOVE, jQuery, document, postboxes, pagenow*/
jQuery(document).ready(function () {
    jQuery('button[value="bulk-move-tags"], button[value="bulk-move-cats"], button[value="bulk-move-category-by-tag"]').click(function () {
        return confirm(BULK_MOVE.msg.move_warning);
    });

    // for post boxes
    postboxes.add_postbox_toggles(pagenow);

	/**
	 * Load the Custom terms based on User selected post type.
	 */
	jQuery( 'input[name="smbm_mbct_selected_post_type"]' ).change( function() {
		var data = {
			'action'   : BULK_MOVE.bulk_move_posts.action,
			'security' : BULK_MOVE.bulk_move_posts.security,
			'post_type': this.value
		};
		jQuery.post( ajaxurl, data, function( response ) {
			if ( response.success ) {

				jQuery( '#smbm_mbct_selected_term' ).html( response.data.select_term );
				jQuery( '#smbm_mbct_mapped_term' ).html( response.data.map_term );
			}
		});
	});

});
