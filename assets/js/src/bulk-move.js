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
	/**
	 * Enable select2
	 */
	jQuery( '.select2' ).select2();

	jQuery( '.select2Ajax' ).select2({
		ajax: {
    			url: ajaxurl, 
    			dataType: 'json',
    			delay: 250, 
    			data: function (params) {
    				var term = jQuery(this).attr('data-term');
      				return {
        				'q': params.term, 
        				'term': term,
        				'action': BULK_MOVE.bulk_move_posts_taxonomy.load_taxonomy_action,
        				'nonce': BULK_MOVE.bulk_move_posts_taxonomy.nonce,
      				};
    			},
    			processResults: function( data ) {
				var options = [];
				if ( data ) {
 
					jQuery.each( data, function( index, text ) { 
						options.push( { id: text[0], text: text[1] } );
					});
 
				}
				return {
					results: options
				};
			},
			cache: true
		},
		minimumInputLength: 2 // the minimum of symbols to input before perform a search
	});
	
    /**
     * Gets the value of the selected element & trims the value.
     *
     * @param   selectedElem
     * @returns {String} Returns empty string when element doesn't exist.
     *          Otherwise returns the element's value.
     */
    var getValAndTrim = function (selectedElem) {

        if (selectedElem.length === 0) {
            return '';
        }

        return selectedElem.val();
    };

    /**
     * Validates to TRUE when the same User role is selected on
     * both sides during Bulk Move Users.
     *
     * @returns {boolean}
     */
    BULK_MOVE.validate_same_user_roles = function () {

        var fromUserRole = getValAndTrim(jQuery('#bm-users-by-role-from-roles-list')),
            toUserRole = getValAndTrim(jQuery('#bm-users-by-role-to-roles-list'));

        return fromUserRole.toLowerCase() === toUserRole.toLowerCase();
    };

    jQuery('button[value="move_tags"], button[value="move_cats"], button[value="move_category_by_tag"], button[value="move_users_by_role"], button[value="move_custom_taxonomy"]').click(function (e) {

        var currentButton = jQuery(this).val(),
            valid = true,
            errorKey;

        if (!BULK_MOVE.validators.hasOwnProperty(currentButton)) {
            return confirm(BULK_MOVE.msg.move_warning);
        }

        jQuery.each(BULK_MOVE.validators[currentButton], function (index, validator) {
            valid = valid && (!BULK_MOVE[validator]());

            if (!valid) {
                e.preventDefault();
                errorKey = validator.replace('validate_', '');
                alert(BULK_MOVE['error'][errorKey]);
                return false;
            }
        });

        if (valid) {
            return confirm(BULK_MOVE.msg.move_warning);
        }
    });

	// Enable toggles for all modules.
	postboxes.add_postbox_toggles( pagenow );

	jQuery( 'tr.taxonomy-select-row, tr.term-select-row, .bm_ct_filters, .bm_ct_submit' ).hide();

	/**
	 * Load Taxonomy on Post Type change.
	 */
	jQuery( '#smbm_move_custom_taxonomy_post_type' ).change( function () {
		var selectedPostType = jQuery( this ).val(),
			payload = {
				'action'    : BULK_MOVE.bulk_move_posts.get_taxonomy_action,
				'nonce'     : BULK_MOVE.bulk_move_posts.nonce,
				'post_type' : selectedPostType
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
				'action'   : BULK_MOVE.bulk_move_posts.get_terms_action,
				'nonce'    : BULK_MOVE.bulk_move_posts.nonce,
				'taxonomy' : selectedTaxonomy
			};

		if ( '-1' === selectedTaxonomy ) {
			jQuery( 'tr.term-select-row, .bm_ct_filters, .bm_ct_submit' ).hide();

			return;
		}
		jQuery( 'tr.term-select-row' ).show();
		jQuery( '#smbm_mbct_selected_term, #smbm_mbct_mapped_term' ).select2({
			ajax: {
    			url: ajaxurl, 
    			dataType: 'json',
    			delay: 250, 
    			data: function (params) {
    				var term = selectedTaxonomy;
      				return {
        				'q': params.term, 
        				'term': term,
        				'action': BULK_MOVE.bulk_move_posts_taxonomy.load_taxonomy_action,
        				'nonce': BULK_MOVE.bulk_move_posts_taxonomy.nonce,
      				};
    			},
    			processResults: function( data ) {
					var options = [];
					if ( data ) {
	 
						jQuery.each( data, function( index, text ) { 
							options.push( { id: text[0], text: text[1] } );
						});
	 
					}
					return {
						results: options
					};
				},
				cache: true
			},
			width: '300px',
			minimumInputLength: 3 // the minimum of symbols to input before perform a search
		});

	});

	jQuery( '#smbm_mbct_selected_term, #smbm_mbct_mapped_term' ).change( function() {
		jQuery( '.bm_ct_filters, .bm_ct_submit' ).show();
	});

});
