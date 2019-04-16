<?php

namespace BulkWP\BulkMove\Core\Modules\Post;

use BulkWP\BulkMove\Core\Modules\BaseModule;
use BulkWP\BulkMove\Core\Actions\LoadTaxonomyTermAction;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * A Bulk Move Metabox module that is embedded inside a Post page.
 *
 * Create a subclass to create Post metabox modules.
 *
 * @since 2.0.0
 */
abstract class PostModule extends BaseModule {
	/**
	 * Renders the categories select.
	 *
	 * @param string $name Select tag's name.
	 *
	 * @since 2.0.0
	 *
	 */
	protected function render_categories_dropdown( $name ) {

		$bm_select2_ajax_limit_categories = apply_filters( 'bm_select2_ajax_limit_categories', LoadTaxonomyTermAction::BM_MAX_SELECT2_LIMIT );

		$categories = get_categories( array(
				'hide_empty' => false,
				'number'     => $bm_select2_ajax_limit_categories,
			)
		);
		if ( count( $categories ) >= $bm_select2_ajax_limit_categories ) { ?>
			<select class="select2Ajax" name="<?php echo sanitize_html_class( $name ); ?>" data-term="category"
			        data-placeholder="<?php _e( 'Select MoveCategoryModule', 'bulk-move' ); ?>" style="width:300px">
			</select>
		<?php } else {
			?>
			<select class="select2" name="<?php echo sanitize_html_class( $name ); ?>"
			        data-placeholder="<?php _e( 'Select MoveCategoryModule', 'bulk-move' ); ?>">
				<?php foreach ( $categories as $category ) { ?>
					<option value="<?php echo $category->cat_ID; ?>"><?php echo $category->cat_name, ' (', $category->count, ' ', __( 'Posts', 'bulk-move' ), ')'; ?></option>
				<?php } ?>
			</select>
		<?php }
	}
}
