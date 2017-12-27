<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Bulk Move Loadie interface.
 * The `load()` method of this interface will be called by BulkMove.
 * Even though Loadie is not an actual word it sound more logical than subscriber.
 *
 * @since 2.0.0
 */
interface BM_Loadie {

	/**
	 * This method will be called by Bulk Move before `bm_loaded` event.
	 *
	 * @return void
	 */
	public function load();
}
