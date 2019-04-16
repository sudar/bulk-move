<?php

namespace BulkWP\BulkMove\Core\Modules\User;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Metabox to move posts based on category.
 *
 * @since 2.0.0
 */
class MoveRoleModule extends UserModule {

	public function render() {
		?>
		<!-- Role Start-->
		<h4>
			<?php
			_e( 'On the left side, select the user role from which you want to move users.', 'bulk-move' );
			_e( 'In the right side select the user role to which you want the users to be moved.', 'bulk-move' );
			?>
		</h4>

		<fieldset class="options">
			<table class="optiontable">
				<tr>
					<td scope="row">
						<?php $this->render_roles_dropdown( 'from' ); ?>
						==>
					</td>
					<td scope="row">
						<?php $this->render_roles_dropdown( 'to', true ); ?>
					</td>
				</tr>
			</table>

		</fieldset>

		<?php $this->render_submit(); ?>
		<!-- Role end-->
		<?php
	}

	public function move( $options ) {
		$args  = array(
			'role' => $options['old_role'],
		);
		$users = get_users( $args );

		foreach ( $users as $user ) {
			if ( ! $user instanceof \WP_User ) {
				continue;
			}
			$user->set_role( $options['new_role'] );
		}

		return count( $users );
	}

	protected function initialize() {
		$this->module_slug           = 'bm-users-by-role';
		$this->messages['box_label'] = __( 'Move Users By Role', 'bulk-move' );
		$this->action                = 'move_users_by_role';
	}

	protected function convert_user_input_to_options( $request ) {
		$options             = array();
		$options['old_role'] = $request[ $this->module_slug . '-from-roles-list' ];
		$options['new_role'] = $request[ $this->module_slug . '-to-roles-list' ];

		return $options;
	}

	protected function get_success_message( $posts_moved ) {
		/* translators: 1 Number of posts moved */
		return _n( 'Moved %d user between the selected roles', 'Moved %d users between the selected roles.', $posts_moved, 'bulk-move' );
	}

	/**
	 * Filter the js array.
	 *
	 * @since 2.0.0
	 *
	 * @param array $js_array JavaScript Array.
	 *
	 * @return array Modified JavaScript Array
	 */
	public function filter_js_array( $js_array ) {
		$js_array['msg']['move_administrators_warning'] = __( 'Are you sure you want to move all Administrators?', 'bulk-move' );
		$js_array['msg']['move_warning']                = __( 'Are you sure you want to move all the users in the selected role?', 'bulk-move' );
		$js_array['error']['same_user_roles']           = __( 'You cannot move users to the same role.', 'bulk-move' );
		$js_array['validators'][ $this->action ]        = array( 'validate_same_user_roles' );

		return $js_array;
	}
}
