/**
 * JavaScript for Bulk move Plugin
 *
 * http://sudarmuthu.com/wordpress/bulk-move
 *
 * @author: Sudar <http://sudarmuthu.com>
 */

/*jslint browser: true, devel: true*/
/*global BULK_MOVE, jQuery, document, postboxes, pagenow, ajaxurl*/
jQuery(document).ready(function () {
    jQuery( 'button[value="move_tags"], button[value="move_cats"], button[value="move_category_by_tag"], button[value="move_custom_taxonomy"]' ).click( function () {
        return confirm( BULK_MOVE.msg.move_warning );
    });

    // Enable toggles for all modules.
    postboxes.add_postbox_toggles( pagenow );

	jQuery( 'tr.taxonomy-select-row, tr.term-select-row, .bm_ct_filters, .bm_ct_submit' ).hide();

	/**
	 * Load Taxonomy on Post Type change.
	 */
	jQuery( '#smbm_mbct_post_type' ).change( function () {
		var selectedOption = jQuery( this ).val(),
			data = {
				'action'   : BULK_MOVE.bulk_move_posts.action_get_taxonomy,
				'nonce'    : BULK_MOVE.bulk_move_posts.nonce,
				'post_type': selectedOption
			};

		jQuery( 'tr.term-select-row' ).hide();

		if ( 'select' === selectedOption.toLowerCase() ) {
			jQuery( 'tr.taxonomy-select-row' ).hide();
		} else {
			jQuery.ajaxSetup( { async: false } );
			jQuery.post( ajaxurl, data, function( response ) {
				if ( response.success ) {
					jQuery( '#smbm_mbct_taxonomy' ).html( response.data );
					jQuery( 'tr.taxonomy-select-row' ).show();
				}
			});
		}
	});

	/**
	 * Load Term on Taxonomy change.
	 */
	jQuery( '#smbm_mbct_taxonomy' ).change( function () {

		//  Selected option.
		var selectedOption = jQuery( this ).val(),
			// Data to send via AJAX.
			data = {
				'action'   : BULK_MOVE.bulk_move_posts.action_get_terms,
				'security' : BULK_MOVE.bulk_move_posts.security,
				'taxonomy' : selectedOption
			};

		if ( 'select' === selectedOption.toLowerCase() ) {
			jQuery( 'tr.term-select-row' ).hide();
		} else {
			jQuery.ajaxSetup( { async: false } );
			jQuery.post( ajaxurl, data, function( response ) {
				if ( response.success ) {
					jQuery( '#smbm_mbct_selected_term' ).html( response.data.select_term );
					jQuery( '#smbm_mbct_mapped_term' ).html( response.data.map_term );
					jQuery( 'tr.term-select-row' ).show();
				}
			});
		}
	});
});
