<?php

/**
 * Test Move Cats functionality.
 */
class MoveCatsTest extends WP_UnitTestCase {

	/**
	 * Test basic case of moving categories.
	 */
	public function test_move_posts_from_one_cat_to_another() {
		// Create two categories.
		$cat1 = $this->factory->category->create( array( 'name' => 'cat1' ) );
		$cat2 = $this->factory->category->create( array( 'name' => 'cat2' ) );

		// Create one post in each category.
		$post1 = $this->factory->post->create( array( 'post_title' => 'post1', 'post_category' => array( $cat1 ) ) );
		$post2 = $this->factory->post->create( array( 'post_title' => 'post2', 'post_category' => array( $cat2 ) ) );

		// Assert that each category has one post.
		$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
		$posts_in_cat2 = $this->get_posts_by_category( $cat2 );

		$this->assertEquals( count( $posts_in_cat1 ), 1 );
		$this->assertEquals( count( $posts_in_cat2 ), 1 );

		// call our method.
		Bulk_Move_Posts::do_move_cats( $cat1, $cat2, true );

		// Assert that category 1 has no posts.
		$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
		$this->assertEquals( count( $posts_in_cat1 ), 0 );

		// Assert that category 2 has two posts.
		$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
		$this->assertEquals( count( $posts_in_cat2 ), 2 );
	}

	/**
	 * Test moving posts from one category to another with overwrite.
	 */
	public function test_move_posts_from_one_cat_to_another_with_overwrite() {
		// Create two categories and a common category.
		$cat1 = $this->factory->category->create( array( 'name' => 'cat1' ) );
		$cat2 = $this->factory->category->create( array( 'name' => 'cat2' ) );
		$common_cat = $this->factory->category->create( array( 'name' => 'common_cat' ) );

		// Create one post in each category.
		// The first post will also have the common category.
		$post1 = $this->factory->post->create( array( 'post_title' => 'post1', 'post_category' => array( $cat1, $common_cat ) ) );
		$post2 = $this->factory->post->create( array( 'post_title' => 'post2', 'post_category' => array( $cat2 ) ) );

		// Assert that each category has one post.
		$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
		$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
		$posts_in_common_cat = $this->get_posts_by_category( $common_cat );

		$this->assertEquals( count( $posts_in_cat1 ), 1 );
		$this->assertEquals( count( $posts_in_cat2 ), 1 );
		$this->assertEquals( count( $posts_in_common_cat ), 1 );

		// Invoke our method.
		Bulk_Move_Posts::do_move_cats( $cat1, $cat2, true );

		// Assert that category 1 has no posts.
		$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
		$this->assertEquals( count( $posts_in_cat1 ), 0 );

		// Assert that common category has no posts.
		$posts_in_common_cat = $this->get_posts_by_category( $common_cat );
		$this->assertEquals( count( $posts_in_common_cat ), 0 );

		// Assert that category 2 has two posts.
		$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
		$this->assertEquals( count( $posts_in_cat2 ), 2 );
	}

	/**
	 * Test Moving category without overwrite.
	 */
	public function test_move_posts_from_one_cat_to_another_without_overwrite() {
		// Create two categories and a common category.
		$cat1 = $this->factory->category->create( array( 'name' => 'cat1' ) );
		$cat2 = $this->factory->category->create( array( 'name' => 'cat2' ) );
		$common_cat = $this->factory->category->create( array( 'name' => 'common_cat' ) );

		// Create one post in each category.
		// The first post will also have the common category.
		$post1 = $this->factory->post->create( array( 'post_title' => 'post1', 'post_category' => array( $cat1, $common_cat ) ) );
		$post2 = $this->factory->post->create( array( 'post_title' => 'post2', 'post_category' => array( $cat2 ) ) );

		// Assert that each cateogry has one post.
		$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
		$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
		$posts_in_common_cat = $this->get_posts_by_category( $common_cat );

		$this->assertEquals( count( $posts_in_cat1 ), 1 );
		$this->assertEquals( count( $posts_in_cat2 ), 1 );
		$this->assertEquals( count( $posts_in_common_cat ), 1 );

		// Invoke our method.
		Bulk_Move_Posts::do_move_cats( $cat1, $cat2, false );

		// Assert that category 1 has no posts.
		$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
		$this->assertEquals( count( $posts_in_cat1 ), 0 );

		// Assert that common category has one posts.
		$posts_in_common_cat = $this->get_posts_by_category( $common_cat );
		$this->assertEquals( count( $posts_in_common_cat ), 1 );

		// Assert that category 2 has two posts.
		$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
		$this->assertEquals( count( $posts_in_cat2 ), 2 );
	}

	/**
	 * Helper method to get posts by category.
	 *
	 * @param string $cat Category name.
	 *
	 * @return array Posts that belong to that category.
	 */
	protected function get_posts_by_category( $cat ) {
		$args = array(
			'category__in' => array( $cat ),
			'post_type'    => 'post',
			'nopaging'     => 'true',
		);

		$wp_query = new WP_Query();
		return $wp_query->query( $args );
	}
}
