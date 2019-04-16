<?php

namespace BulkWP\BulkMove\Core\Modules\User;

use BulkWP\BulkMove\Core\Modules\BaseModule;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * A Bulk Move Metabox module that is embedded inside a User page.
 *
 * Create a subclass to create User metabox modules.
 *
 * @since 2.0.0
 */
abstract class UserModule extends BaseModule {
	/**
	 * Renders the User roles dropdown.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name                Select tag's name & ID attribute.
	 * @param bool   $hide_no_role_option Default FALSE.
	 *                                    Flag to hide the no role option.
	 */
	protected function render_roles_dropdown( $name = '', $hide_no_role_option = false ) {
		global $wp_roles;
		$roles = $wp_roles->roles;

		if ( ! ( $users = wp_cache_get( 'bm_users', 'bulk-wp' ) ) ) {
			$users = get_users();
			wp_cache_add( 'bm_users', 'bulk-wp' );
		}

		$users_by_roles = $this->get_users_count_by_roles( $users, $roles );

		$field_name = $this->meta_box_slug;
		$field_name .= ! empty( $name ) ? '-' . $name : $name;
		$field_name .= '-roles-list';

		?>
		<select id="<?php echo sanitize_html_class( $field_name ); ?>"
		        name="<?php echo sanitize_html_class( $field_name ); ?>">
			<?php if ( ! $hide_no_role_option ) : ?>
				<option value="norole">
					<?php echo __( 'No role', 'bulk-move' ); ?>
					<?php echo '(' . $users_by_roles['norole'] . ')' ?>
				</option>
			<?php endif; ?>
			<?php
			foreach ( $roles as $role_slug => $role ) :
				?>
				<option value="<?php echo esc_attr( $role_slug ); ?>">
					<?php echo esc_html( $role['name'] ); ?>
					&nbsp;<?php echo '(' . $users_by_roles[ $role_slug ] . ')' ?>
				</option>
			<?php
			endforeach;
			?>
		</select>
		<?php
	}

	/**
	 * Gets the Users count by Role.
	 *
	 * @param \WP_User[] $users List of Users.
	 * @param array      $roles List of Roles.
	 *
	 * @return array
	 */
	public function get_users_count_by_roles( $users, $roles ) {
		$users_by_roles           = array();
		$users_by_roles['norole'] = 0;

		foreach ( $roles as $role => $role_arr ) {
			$role_count = 0;

			foreach ( $users as $user ) {
				if ( ! $user instanceof \WP_User ) {
					continue;
				}

				if ( empty( $user->roles ) ) {
					$users_by_roles['norole'] ++;

					continue;
				}

				$users_by_roles[ $role ] = in_array( $role, $user->roles ) ? ++ $role_count : $role_count;
			}

		}

		return $users_by_roles;
	}
}
