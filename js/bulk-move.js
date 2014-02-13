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
});
