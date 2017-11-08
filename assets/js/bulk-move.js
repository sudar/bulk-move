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

		if ( selectedOption !== '-1' ) {
			jQuery.ajaxSetup( { async: false } );
			jQuery.post( ajaxurl, data, function( response ) {
				jQuery( 'tr.taxonomy-select-row' ).hide();
				if ( response.success ) {

					var taxonomy = response.data.taxonomy || {},
						message  = response.data.no_taxonomy_alert_msg;

					if ( jQuery.isEmptyObject( taxonomy ) ) {
						alert( message );
					} else {
						jQuery( 'tr.taxonomy-select-row' ).show();

						// Reset options on each AJAX request.
						jQuery( '#smbm_mbct_taxonomy' ).children( 'option' ).remove();

						jQuery( '<option/>', {
							'value': '-1',
							'text': response.data.default_select_taxonomy_label
						}).appendTo( '#smbm_mbct_taxonomy' );

						jQuery.each( taxonomy, function( index, val ) {
							jQuery( '<option/>', {
								'value': val,
								'text': val
							}).appendTo( '#smbm_mbct_taxonomy' );
						});
					}
				}
			});
		} else {
			jQuery( 'tr.taxonomy-select-row' ).hide();
			jQuery( 'tr.term-select-row' ).hide();
			jQuery( '.bm_ct_filters' ).hide();
			jQuery( '.bm_ct_submit' ).hide();
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
				'nonce'    : BULK_MOVE.bulk_move_posts.nonce,
				'taxonomy' : selectedOption
			};

		if ( selectedOption !== '-1' ) {
			jQuery.ajaxSetup( { async: false } );
			jQuery.post( ajaxurl, data, function( response ) {

				if ( response.success ) {

					var term     = response.data.term || {},
						message  = response.data.no_term_alert_msg,
						termIds  = Object.keys( term )

					if ( jQuery.isEmptyObject( term ) ) {
						alert( message );
					} else {
						jQuery( 'tr.term-select-row' ).show();
						// Reset options on each AJAX request.
						jQuery( '#smbm_mbct_selected_term, #smbm_mbct_mapped_term' ).children( 'option' ).remove();

						jQuery( '<option/>', {
							'value': '-1',
							'text': response.data.default_select_term_label
						}).appendTo( '#smbm_mbct_selected_term' );

						jQuery( '<option/>', {
							'value': '-1',
							'text': response.data.default_remove_term_label
						}).appendTo( '#smbm_mbct_mapped_term' );

						jQuery.each( termIds, function( index, val ) {
							console.log(  term[ val ]['term_name'] + ':' + term[ val ]['term_count'] );

							jQuery( '<option/>', {
								'value': val,
								'text': term[ val ]['term_name'] + '(' + term[ val ]['term_count'] + ')'
							}).appendTo( '#smbm_mbct_selected_term' );

							jQuery( '<option/>', {
								'value': val,
								'text': term[ val ]['term_name'] + '(' + term[ val ]['term_count'] + ')'
							}).appendTo( '#smbm_mbct_mapped_term' );
						});
					}
				}
			});
		} else {
			jQuery( 'tr.term-select-row' ).hide();
			jQuery( '.bm_ct_filters' ).hide();
			jQuery( '.bm_ct_submit' ).hide();
		}
	});

	jQuery( '#smbm_mbct_selected_term, #smbm_mbct_mapped_term' ).change( function() {
		jQuery( '.bm_ct_filters' ).show();
		jQuery( '.bm_ct_submit' ).show();
	});

});
