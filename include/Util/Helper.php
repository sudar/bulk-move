<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

class BM_Util_helper {
	public function get_users_count_by_roles( $users, $roles ) {
		$users_by_roles           = array();
		$users_by_roles['norole'] = 0;

		foreach ( $roles as $role => $role_arr ) {
			$role_count = 0;

			foreach ( $users as $user ) {
				if ( ! $user instanceof WP_User ) {
					continue;
				}

				if ( empty ( $user->roles ) ) {
					$users_by_roles['norole'] ++;
					continue;
				}

				$users_by_roles[ $role ] = in_array( $role, $user->roles ) ? ++ $role_count : $role_count;
			}

		}

		return $users_by_roles;
	}
}

