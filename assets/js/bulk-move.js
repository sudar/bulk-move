/**
 * JavaScript for Bulk move Plugin
 *
 * http://sudarmuthu.com/wordpress/bulk-move
 *
 * @author: Sudar <http://sudarmuthu.com>
 */

/*jslint browser: true, devel: true*/
/*global BULK_MOVE, jQuery, document, postboxes, pagenow, ajaxurl*/
BULK_MOVE.validate_same_user_roles = function(that) {

    var fromUserRole = jQuery( '#bm-users-by-role-from-roles-list' ).val().toLowerCase(),
        ToUserRole = jQuery( '#bm-users-by-role-to-roles-list' ).val().toLowerCase();

    fromUserRole = jQuery.trim( fromUserRole );
    ToUserRole = jQuery.trim( ToUserRole );

    return fromUserRole === ToUserRole;
};

jQuery(document).ready(function () {

    jQuery('button[value="move_tags"], button[value="move_cats"], button[value="move_category_by_tag"], button[value="move_custom_taxonomy"], button[value="move_users_by_role"]').click(function (e) {

        var currentButton = jQuery(this).val(),
            valid = true,
            that = this,
            errorKey;

        if (!currentButton in BULK_MOVE.validators) {
            return confirm(BULK_MOVE.msg.move_warning);
        }

        jQuery.each(BULK_MOVE.validators[currentButton], function (index, validator) {
            valid = valid && (!BULK_MOVE[validator](that));

            if (!valid) {
                errorKey = validator.replace('validate_', '');
                alert(BULK_MOVE['error'][errorKey]);
                return false;
            }
        });

        if ( valid ) {
            return confirm(BULK_MOVE.msg.move_warning);
        } else {
            e.preventDefault();
        }
    });

	// Enable toggles for all modules.
	postboxes.add_postbox_toggles( pagenow );

	jQuery( 'tr.taxonomy-select-row, tr.term-select-row, .bm_ct_filters, .bm_ct_submit' ).hide();

	/**
	 * Load Taxonomy on Post Type change.
	 */
	jQuery( '#smbm_mbct_post_type' ).change( function () {
		var selectedPostType = jQuery( this ).val(),
			payload = {
				'action'   : BULK_MOVE.bulk_move_posts.action_get_taxonomy,
				'nonce'    : BULK_MOVE.bulk_move_posts.nonce,
				'post_type': selectedPostType
			};

		if ( '-1' === selectedPostType ) {
			jQuery( 'tr.taxonomy-select-row, tr.term-select-row, .bm_ct_filters, .bm_ct_submit' ).hide();

			return;
		}

		jQuery.ajaxSetup( { async: false } );

		jQuery.post( ajaxurl, payload, function( response ) {
			jQuery( 'tr.taxonomy-select-row' ).hide();

			if ( ! response.success ) {
				return;
			}

			var taxonomies = response.data.taxonomies || {};

			if ( jQuery.isEmptyObject( taxonomies ) ) {
				alert( response.data.no_taxonomy_msg );
				return;
			}

			jQuery( 'tr.taxonomy-select-row' ).show();

			// Reset options on each AJAX request.
			jQuery( '#smbm_mbct_taxonomy' ).children( 'option' ).remove();

			jQuery( '<option/>', {
				'value': '-1',
				'text': response.data.select_taxonomy_label
			}).appendTo( '#smbm_mbct_taxonomy' );

			jQuery.each( taxonomies, function( index, taxonomy ) {
				jQuery( '<option/>', {
					'value': taxonomy,
					'text': taxonomy
				}).appendTo( '#smbm_mbct_taxonomy' );
			});
		});
	});

	/**
	 * Load Term on Taxonomy change.
	 */
	jQuery( '#smbm_mbct_taxonomy' ).change( function () {

		var selectedTaxonomy = jQuery( this ).val(),
			payload = {
				'action'   : BULK_MOVE.bulk_move_posts.action_get_terms,
				'nonce'    : BULK_MOVE.bulk_move_posts.nonce,
				'taxonomy' : selectedTaxonomy
			};

		if ( '-1' === selectedTaxonomy ) {
			jQuery( 'tr.term-select-row, .bm_ct_filters, .bm_ct_submit' ).hide();

			return;
		}

		jQuery.ajaxSetup( { async: false } );

		jQuery.post( ajaxurl, payload, function( response ) {

			if ( ! response.success ) {
				return;
			}

			var terms = response.data.terms || {};

			if ( jQuery.isEmptyObject( terms ) ) {
				alert( response.data.no_term_msg );

				return;
			}

			jQuery( 'tr.term-select-row' ).show();

			// Reset options on each AJAX request.
			jQuery( '#smbm_mbct_selected_term, #smbm_mbct_mapped_term' ).children( 'option' ).remove();

			jQuery( '<option/>', {
				'value': '-1',
				'text': response.data.select_term_label
			}).appendTo( '#smbm_mbct_selected_term' );

			jQuery( '<option/>', {
				'value': '-1',
				'text': response.data.remove_term_label
			}).appendTo( '#smbm_mbct_mapped_term' );

			jQuery.each( terms, function( termId, term ) {
				jQuery( '<option/>', {
					'value': termId,
					'text': term['term_name']
				}).appendTo( '#smbm_mbct_selected_term' );

				jQuery( '<option/>', {
					'value': termId,
					'text': term['term_name']
				}).appendTo( '#smbm_mbct_mapped_term' );
			});
		});
	});

	jQuery( '#smbm_mbct_selected_term, #smbm_mbct_mapped_term' ).change( function() {
		jQuery( '.bm_ct_filters, .bm_ct_submit' ).show();
	});


});
