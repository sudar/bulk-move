<?php

use BulkWP\Tests\WPCore\WPCoreUnitTestCase;

/**
 * Test BM_Metabox_Posts_Category class.
 *
 * TODO: Add tests for default category.
 */
class CategoryTest extends WPCoreUnitTestCase {

	/**
	 * @var \BM_Metabox_Posts_Category
	 */
	protected $category_metabox;

	public function setUp() {
		parent::setUp();

		$this->category_metabox = new \BM_Metabox_Posts_Category();
	}

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

		$this->assertEquals( 1, count( $posts_in_cat1 ) );
		$this->assertEquals( 1, count( $posts_in_cat2 ) );

		// call our method.
		$options = array(
			'old_cat'   => $cat1,
			'new_cat'   => $cat2,
			'overwrite' => true,
		);

		$move_result = $this->category_metabox->move( $options );
		
		if ( ! is_wp_error( $move_result ) ) {
			// Assert that category 1 has no posts.
			$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
			$this->assertEquals( 0, count( $posts_in_cat1 ) );

			// Assert that category 2 has two posts.
			$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
			$this->assertEquals( 2, count( $posts_in_cat2 ) );
		} else {
			echo $move_result->get_error_message();
		}
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

		$this->assertEquals( 1, count( $posts_in_cat1 ) );
		$this->assertEquals( 1, count( $posts_in_cat2 ) );
		$this->assertEquals( 1, count( $posts_in_common_cat ) );

		// Invoke our method.
		$options = array(
			'old_cat'   => $cat1,
			'new_cat'   => $cat2,
			'overwrite' => true,
		);

		$move_result = $this->category_metabox->move( $options );

		if ( ! is_wp_error( $move_result ) ) {
			// Assert that category 1 has no posts.
			$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
			$this->assertEquals( 0, count( $posts_in_cat1 ) );

			// Assert that common category has no posts.
			$posts_in_common_cat = $this->get_posts_by_category( $common_cat );
			$this->assertEquals( 0, count( $posts_in_common_cat ) );

			// Assert that category 2 has two posts.
			$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
			$this->assertEquals( 2, count( $posts_in_cat2 ) );
		} else {
			echo $move_result->get_error_message();
		}
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

		$this->assertEquals( 1, count( $posts_in_cat1 ) );
		$this->assertEquals( 1, count( $posts_in_cat2 ) );
		$this->assertEquals( 1, count( $posts_in_common_cat ) );

		// Invoke our method.
		$options = array(
			'old_cat'   => $cat1,
			'new_cat'   => $cat2,
			'overwrite' => false,
		);

		$move_result = $this->category_metabox->move( $options );

		if ( ! is_wp_error( $move_result ) ) {
			// Assert that category 1 has no posts.
			$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
			$this->assertEquals( 0, count( $posts_in_cat1 ) );

			// Assert that common category has one posts.
			$posts_in_common_cat = $this->get_posts_by_category( $common_cat );
			$this->assertEquals( 1, count( $posts_in_common_cat ) );

			// Assert that category 2 has two posts.
			$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
			$this->assertEquals( 2, count( $posts_in_cat2 ) );
		} else {
			echo $move_result->get_error_message();
		}
	}

	/**
	 * Test remove category from post
	 */
	public function test_remove_category_from_posts(){
		// Create two categories.
		$cat1 = $this->factory->category->create( array( 'name' => 'cat1' ) );
		$cat2 = $this->factory->category->create( array( 'name' => 'cat2' ) );

		// Create one post in each category.
		$post1 = $this->factory->post->create( array( 'post_title' => 'post1', 'post_category' => array( $cat1 ) ) );
		$post2 = $this->factory->post->create( array( 'post_title' => 'post2', 'post_category' => array( $cat2 ) ) );

		// Assert that each category has one post.
		$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
		$posts_in_cat2 = $this->get_posts_by_category( $cat2 );

		$this->assertEquals( 1, count( $posts_in_cat1 ) );
		$this->assertEquals( 1, count( $posts_in_cat2 ) );

		// call our method.
		$options = array(
			'old_cat'   => $cat1,
			'new_cat'   => -1,
			'overwrite' => true,
		);

		$move_result = $this->category_metabox->move( $options );

		if ( ! is_wp_error( $move_result ) ) {
			// Assert that category 1 has no posts.
			$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
			$this->assertEquals( 0, count( $posts_in_cat1 ) );

			// Assert that category 2 has one posts.
			$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
			$this->assertEquals( 1, count( $posts_in_cat2 ) );
		} else {
			echo $move_result->get_error_message();
		}
	}

	/**
	 * Test remove category from post without overwrite.
	 */
	public function test_remove_category_from_posts_without_overwrite() {
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

		$this->assertEquals( 1, count( $posts_in_cat1 ) );
		$this->assertEquals( 1, count( $posts_in_cat2 ) );
		$this->assertEquals( 1, count( $posts_in_common_cat ) );

		// Invoke our method.
		$options = array(
			'old_cat'   => $cat1,
			'new_cat'   => -1,
			'overwrite' => false,
		);

		$move_result = $this->category_metabox->move( $options );

		if ( ! is_wp_error( $move_result ) ) {
			// Assert that category 1 has no posts.
			$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
			$this->assertEquals( 0, count( $posts_in_cat1 ) );

			// Assert that common category has one posts.
			$posts_in_common_cat = $this->get_posts_by_category( $common_cat );
			$this->assertEquals( 1, count( $posts_in_common_cat ) );

			// Assert that category 2 has one posts.
			$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
			$this->assertEquals( 1, count( $posts_in_cat2 ) );
		} else {
			echo $move_result->get_error_message();
		}
	}

	/**
	 * Test remove category from post with overwrite.
	 */
	public function test_remove_category_from_posts_with_overwrite() {
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

		$this->assertEquals( 1, count( $posts_in_cat1 ) );
		$this->assertEquals( 1, count( $posts_in_cat2 ) );
		$this->assertEquals( 1, count( $posts_in_common_cat ) );

		// Invoke our method.
		$options = array(
			'old_cat'   => $cat1,
			'new_cat'   => -1,
			'overwrite' => true,
		);

		$move_result = $this->category_metabox->move( $options );

		if ( ! is_wp_error( $move_result ) ) {
			// Assert that category 1 has no posts.
			$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
			$this->assertEquals( 0, count( $posts_in_cat1 ) );

			// Assert that common category has no posts.
			$posts_in_common_cat = $this->get_posts_by_category( $common_cat );
			$this->assertEquals( 0, count( $posts_in_common_cat ) );

			// Assert that category 2 has one posts.
			$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
			$this->assertEquals( 1, count( $posts_in_cat2 ) );
		} else {
			echo $move_result->get_error_message();
		}
	}

	/**
	 * Test basic case of moving default category.
	 */
	public function test_move_posts_from_default_cat_to_another() {
		// Create one categories and get default category.
		$default_cat = get_option( 'default_category' );
		$cat2 = $this->factory->category->create( array( 'name' => 'cat2' ) );

		// Create one post in each category.
		$post1 = $this->factory->post->create( array( 'post_title' => 'post1', 'post_category' => array( $default_cat ) ) );
		$post2 = $this->factory->post->create( array( 'post_title' => 'post2', 'post_category' => array( $cat2 ) ) );

		// Assert that each category has one post.
		$posts_in_default_cat = $this->get_posts_by_category( $default_cat );
		$posts_in_cat2 = $this->get_posts_by_category( $cat2 );

		$this->assertEquals( 1, count( $posts_in_default_cat ) );
		$this->assertEquals( 1, count( $posts_in_cat2 ) );

		// call our method.
		$options = array(
			'old_cat'   => $default_cat,
			'new_cat'   => $cat2,
			'overwrite' => true,
		);

		$move_result = $this->category_metabox->move( $options );

		if ( ! is_wp_error( $move_result ) ) {
			// Assert that default category has no posts.
			$posts_in_default_cat = $this->get_posts_by_category( $default_cat );
			$this->assertEquals( 0, count( $posts_in_default_cat ) );

			// Assert that category 2 has two posts.
			$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
			$this->assertEquals( 2, count( $posts_in_cat2 ) );
		} else {
			echo $move_result->get_error_message();
		}
	}

	/**
	 * Test basic case of moving default category with overwrite.
	 */
	public function test_move_posts_from_default_cat_to_another_with_overwrite() {
		// Create two categories and get default category.
		$default_cat = get_option( 'default_category' );
		$cat2 = $this->factory->category->create( array( 'name' => 'cat2' ) );
		$common_cat = $this->factory->category->create( array( 'name' => 'common_cat' ) );

		// Create one post in each category.
		// The first post will also have the common category.
		$post1 = $this->factory->post->create( array( 'post_title' => 'post1', 'post_category' => array( $default_cat, $common_cat ) ) );
		$post2 = $this->factory->post->create( array( 'post_title' => 'post2', 'post_category' => array( $cat2 ) ) );

		// Assert that each category has one post.
		$posts_in_default_cat = $this->get_posts_by_category( $default_cat );
		$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
		$posts_in_common_cat = $this->get_posts_by_category( $common_cat );

		$this->assertEquals( 1, count( $posts_in_default_cat ) );
		$this->assertEquals( 1, count( $posts_in_cat2 ) );
		$this->assertEquals( 1, count( $posts_in_common_cat ) );

		// call our method.
		$options = array(
			'old_cat'   => $default_cat,
			'new_cat'   => $cat2,
			'overwrite' => true,
		);

		$move_result = $this->category_metabox->move( $options );

		if ( ! is_wp_error( $move_result ) ) {
		// Assert that default category has no posts.
		$posts_in_default_cat = $this->get_posts_by_category( $default_cat );
		$this->assertEquals( 0, count( $posts_in_default_cat ) );

		// Assert that common category has no posts.
		$posts_in_common_cat = $this->get_posts_by_category( $common_cat );
		$this->assertEquals( 0, count( $posts_in_common_cat ) );

		// Assert that category 2 has two posts.
		$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
		$this->assertEquals( 2, count( $posts_in_cat2 ) );
		} else {
			echo $move_result->get_error_message();
		}
	}

	/**
	 * Test basic case of moving default category without overwrite.
	 */
	public function test_move_posts_from_default_cat_to_another_without_overwrite() {
		// Create two categories and get default category.
		$default_cat = get_option( 'default_category' );
		$cat2 = $this->factory->category->create( array( 'name' => 'cat2' ) );
		$common_cat = $this->factory->category->create( array( 'name' => 'common_cat' ) );

		// Create one post in each category.
		// The first post will also have the common category.
		$post1 = $this->factory->post->create( array( 'post_title' => 'post1', 'post_category' => array( $default_cat, $common_cat ) ) );
		$post2 = $this->factory->post->create( array( 'post_title' => 'post2', 'post_category' => array( $cat2 ) ) );

		// Assert that each category has one post.
		$posts_in_default_cat = $this->get_posts_by_category( $default_cat );
		$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
		$posts_in_common_cat = $this->get_posts_by_category( $common_cat );

		$this->assertEquals( 1, count( $posts_in_default_cat ) );
		$this->assertEquals( 1, count( $posts_in_cat2 ) );
		$this->assertEquals( 1, count( $posts_in_common_cat ) );

		// call our method.
		$options = array(
			'old_cat'   => $default_cat,
			'new_cat'   => $cat2,
			'overwrite' => false,
		);

		$move_result = $this->category_metabox->move( $options );

		if ( ! is_wp_error( $move_result ) ) {
			// Assert that default category has no posts.
			$posts_in_default_cat = $this->get_posts_by_category( $default_cat );
			$this->assertEquals( 0, count( $posts_in_default_cat ) );

			// Assert that common category has one posts.
			$posts_in_common_cat = $this->get_posts_by_category( $common_cat );
			$this->assertEquals( 1, count( $posts_in_common_cat ) );

			// Assert that category 2 has two posts.
			$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
			$this->assertEquals( 2, count( $posts_in_cat2 ) );
		} else {
			echo $move_result->get_error_message();
		}
	}

	/**
	 * Test remove default category from post
	 */
	public function test_remove_default_category_from_posts(){
		// Get default category.
		$default_cat = get_option( 'default_category' );

		// Create one post.
		$post1 = $this->factory->post->create( array( 'post_title' => 'post1' ) );

		// Assert that default category has one post.
		$posts_in_default_cat = $this->get_posts_by_category( $default_cat );

		$this->assertEquals( 1, count( $posts_in_default_cat ) );

		// call our method.
		$options = array(
			'old_cat'   => $default_cat,
			'new_cat'   => -1,
			'overwrite' => true,
		);

		$move_result = $this->category_metabox->move( $options );

		if ( ! is_wp_error( $move_result ) ) {
			// Assert that default category has one post.
			$posts_in_default_cat = $this->get_posts_by_category( $default_cat );
			$this->assertEquals( 1, count( $posts_in_default_cat ) );
		} else {
			echo $move_result->get_error_message();
		}
	}

	/**
	 * Test remove default category from post with overwrite.
	 */
	public function test_remove_default_category_from_posts_with_overwrite(){
		// Get default category and create common category.
		$default_cat = get_option( 'default_category' );
		$common_cat = $this->factory->category->create( array( 'name' => 'common_cat' ) );

		// Create one post.
		// The post will have both categories.
		$post1 = $this->factory->post->create( array( 'post_title' => 'post1', 'post_category' => array( $default_cat, $common_cat ) ) );

		// Assert that each category has one post.
		$posts_in_default_cat = $this->get_posts_by_category( $default_cat );
		$posts_in_common_cat = $this->get_posts_by_category( $common_cat );

		$this->assertEquals( 1, count( $posts_in_default_cat ) );
		$this->assertEquals( 1, count( $posts_in_common_cat ) );

		// call our method.
		$options = array(
			'old_cat'   => $default_cat,
			'new_cat'   => -1,
			'overwrite' => true,
		);

		$move_result = $this->category_metabox->move( $options );

		if ( ! is_wp_error( $move_result ) ) {
			// Assert that default category has one post.
			$posts_in_default_cat = $this->get_posts_by_category( $default_cat );
			$this->assertEquals( 1, count( $posts_in_default_cat ) );

			// Assert that common category has 0 posts.
			$posts_in_common_cat = $this->get_posts_by_category( $common_cat );
			$this->assertEquals( 0, count( $posts_in_common_cat ) );
		} else {
			echo $move_result->get_error_message();
		}
	}

	/**
	 * Test remove default category from post without overwrite.
	 */
	public function test_remove_default_category_from_posts_without_overwrite(){
		// Get default category and create common category.
		$default_cat = get_option( 'default_category' );
		$common_cat = $this->factory->category->create( array( 'name' => 'common_cat' ) );

		// Create one post.
		// The post will have both categories.
		$post1 = $this->factory->post->create( array( 'post_title' => 'post1', 'post_category' => array( $default_cat, $common_cat ) ) );

		// Assert that each category has one post.
		$posts_in_default_cat = $this->get_posts_by_category( $default_cat );
		$posts_in_common_cat = $this->get_posts_by_category( $common_cat );

		$this->assertEquals( 1, count( $posts_in_default_cat ) );
		$this->assertEquals( 1, count( $posts_in_common_cat ) );

		// call our method.
		$options = array(
			'old_cat'   => $default_cat,
			'new_cat'   => -1,
			'overwrite' => false,
		);

		$move_result = $this->category_metabox->move( $options );

		if ( ! is_wp_error( $move_result ) ) {
			// Assert that default category has 0 post.
			$posts_in_default_cat = $this->get_posts_by_category( $default_cat );
			$this->assertEquals( 0, count( $posts_in_default_cat ) );

			// Assert that common category has one posts.
			$posts_in_common_cat = $this->get_posts_by_category( $common_cat );
			$this->assertEquals( 1, count( $posts_in_common_cat ) );
		} else {
			echo $move_result->get_error_message();
		}
	}
}
