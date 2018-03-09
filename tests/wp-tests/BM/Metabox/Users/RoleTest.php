<?php

use BulkWP\Tests\WPCore\WPCoreUnitTestCase;

/**
 * Test BM_Metabox_Posts_Tag class.
 */
class RoleTest extends WPCoreUnitTestCase {

	/**
	 * @var \BM_Metabox_Users_Role
	 */
	protected $user_metabox;

	protected $role_key         = 'role';
	protected $display_name_key = 'display_name';

	protected $role1;
	protected $role2;

	protected $user1;
	protected $user2;

	protected $capabilities = array(
		'read'         => true,
		'edit_posts'   => true,
		'delete_posts' => false,
	);

	public function setUp() {
		parent::setUp();

		$this->user_metabox = new \BM_Metabox_Users_Role();

		// Create two user roles.
		$this->role1 = array(
			$this->role_key         => 'tester',
			$this->display_name_key => 'Tester',
		);
		$this->role2 = array(
			$this->role_key         => 'qa',
			$this->display_name_key => 'QA',
		);
		add_role( $this->role1[ $this->role_key ], $this->role1[ $this->display_name_key ], $this->capabilities );
		add_role( $this->role2[ $this->role_key ], $this->role2[ $this->display_name_key ], $this->capabilities );

		// Create two Users and assign to a role.
		$this->user1 = wp_create_user( 'user1', 'password', 'user1@bulk-move.tests' );
		$this->user2 = wp_create_user( 'user2', 'password', 'user2@bulk-move.tests' );
		$this->assign_role_by_user_id( $this->user1, $this->role1[ $this->role_key ] );
		$this->assign_role_by_user_id( $this->user2, $this->role1[ $this->role_key ] );
	}

	/**
	 * TearDown
	 */
	public function tearDown() {
		wp_delete_user( $this->user1 );
		wp_delete_user( $this->user2 );
		$this->remove_role( 'tester' );
		$this->remove_role( 'qa' );
	}

	/**
	 * Test basic case of moving Users.
	 */
	public function test_move_users_from_one_role_to_another() {
		$users_in_role1 = get_users( array( 'role' => $this->role1[ $this->role_key ] ) );
		$users_in_role2 = get_users( array( 'role' => $this->role2[ $this->role_key ] ) );

		$this->assertEquals( count( $users_in_role1 ), 2 );
		$this->assertEquals( count( $users_in_role2 ), 0 );

		$options             = array();
		$options['old_role'] = $this->role1[ $this->role_key ];
		$options['new_role'] = $this->role2[ $this->role_key ];

		$this->user_metabox->move( $options );

		$users_in_role1 = get_users( array( 'role' => $this->role1[ $this->role_key ] ) );
		$users_in_role2 = get_users( array( 'role' => $this->role2[ $this->role_key ] ) );

		$this->assertEquals( count( $users_in_role1 ), 0 );
		$this->assertEquals( count( $users_in_role2 ), 2 );
	}
}
