<?php

use BulkWP\Tests\WPCore\WPCoreUnitTestCase;

/**
 * Test BM_Metabox_Posts_CategoryToTagTest class.
 */
class CategoryToTagTest extends WPCoreUnitTestCase {

	/**
	 * @var \BM_Metabox_Posts_CategoryToTag
	 */
	protected $category_to_tag_metabox;

	public function setUp() {
		parent::setUp();

		$this->category_to_tag_metabox = new \BM_Metabox_Posts_CategoryToTag();
	}

	/**
	 * Test basic case of moving category to tag.
	 */
	public function test_move_posts_from_category_to_tag() {
		// Create one tag and category.
		$tag = $this->factory->tag->create( array( 'name' => 'tag' ) );
		$cat = $this->factory->category->create( array( 'name' => 'cat' ) );

		// Create one post in tag and category.
		$post_tag = $this->factory->post->create( array( 'post_title' => 'post_tag' ) );
		wp_set_post_tags( $post_tag, 'tag' );

		$post_cat = $this->factory->post->create( array(
			'post_title'    => 'post_cat',
			'post_category' => array( $cat )
		) );

		// Assert that tag has one post and category has one post.
		$posts_in_tag = $this->get_posts_by_tag( $tag );
		$posts_in_cat = $this->get_posts_by_category( $cat );

		$this->assertEquals( 1, count( $posts_in_tag ) );
		$this->assertEquals( 1, count( $posts_in_cat ) );

		// call our method.
		$options = array(
			'cat'       => $cat,
			'tag'       => $tag,
			'overwrite' => true,
		);

		$move_result = $this->category_to_tag_metabox->move( $options );

		if ( ! is_wp_error( $move_result ) ) {
			// Assert that tag 1 has two posts.
			$posts_in_tag = $this->get_posts_by_tag( $tag );
			$this->assertEquals( 2, count( $posts_in_tag ) );

			// Assert that category has no posts.
			$posts_in_cat = $this->get_posts_by_category( $cat );
			$this->assertEquals( 0, count( $posts_in_cat ) );
		} else {
			echo $move_result->get_error_message();
		}
	}

	/**
	 * Test case of moving category to tag without overwrite.
	 */
	public function test_move_posts_from_category_to_tag_without_overwrite() {
		// Create two tags and one category.
		$cat        = $this->factory->category->create( array( 'name' => 'cat' ) );
		$tag        = $this->factory->tag->create( array( 'name' => 'tag' ) );
		$common_tag = $this->factory->tag->create( array( 'name' => 'common_tag' ) );

		// Create one post in tag and category.
		// The post that contains tag will also have the common tag.
		$post1 = $this->factory->post->create( array( 'post_title' => 'tag_post' ) );
		wp_set_post_tags( $post1, array( 'tag', 'common_tag' ) );

		$post2 = $this->factory->post->create( array( 'post_title' => 'post_cat', 'post_category' => array( $cat ) ) );
		wp_set_post_tags( $post2, array( 'common_tag' ) );

		// Assert that each tag and categories has one post.
		$posts_in_tag        = $this->get_posts_by_tag( $tag );
		$posts_in_cat        = $this->get_posts_by_category( $cat );
		$posts_in_common_tag = $this->get_posts_by_tag( $common_tag );

		$this->assertEquals( 1, count( $posts_in_tag ) );
		$this->assertEquals( 1, count( $posts_in_cat ) );
		$this->assertEquals( 2, count( $posts_in_common_tag ) );

		// call our method.
		$options = array(
			'cat'       => $cat,
			'tag'       => $tag,
			'overwrite' => false,
		);

		$move_result = $this->category_to_tag_metabox->move( $options );

		if ( ! is_wp_error( $move_result ) ) {
			// Assert that tag has two posts.
			$posts_in_tag = $this->get_posts_by_tag( $tag );
			$this->assertEquals( 2, count( $posts_in_tag ) );

			// Assert that category has no posts.
			$posts_in_cat = $this->get_posts_by_category( $cat );
			$this->assertEquals( 0, count( $posts_in_cat ) );

			// Assert that common tag has tow posts.
			$posts_in_common_tag = $this->get_posts_by_tag( $common_tag );
			$this->assertEquals( 2, count( $posts_in_common_tag ) );
		} else {
			echo $move_result->get_error_message();
		}
	}

	/**
	 * Test case of moving category to tag with overwrite.
	 */
	public function test_move_posts_from_category_to_tag_with_overwrite() {
		// Create two tags and one category.
		$cat        = $this->factory->category->create( array( 'name' => 'cat' ) );
		$tag        = $this->factory->tag->create( array( 'name' => 'tag' ) );
		$common_tag = $this->factory->tag->create( array( 'name' => 'common_tag' ) );

		// Create one post in tag and category.
		// The post that contains tag will also have the common tag.
		$post1 = $this->factory->post->create( array( 'post_title' => 'tag_post' ) );
		wp_set_post_tags( $post1, array( 'tag', 'common_tag' ) );

		$post2 = $this->factory->post->create( array( 'post_title' => 'post_cat', 'post_category' => array( $cat ) ) );
		wp_set_post_tags( $post2, array( 'common_tag' ) );

		// Assert that each tag and categories has one post.
		$posts_in_tag        = $this->get_posts_by_tag( $tag );
		$posts_in_cat        = $this->get_posts_by_category( $cat );
		$posts_in_common_tag = $this->get_posts_by_tag( $common_tag );

		$this->assertEquals( 1, count( $posts_in_tag ) );
		$this->assertEquals( 1, count( $posts_in_cat ) );
		$this->assertEquals( 2, count( $posts_in_common_tag ) );

		// call our method.
		$options = array(
			'cat'       => $cat,
			'tag'       => $tag,
			'overwrite' => true,
		);

		$move_result = $this->category_to_tag_metabox->move( $options );

		if ( ! is_wp_error( $move_result ) ) {
			// Assert that tag has two posts.
			$posts_in_tag = $this->get_posts_by_tag( $tag );
			$this->assertEquals( 2, count( $posts_in_tag ) );

			// Assert that category has no posts.
			$posts_in_cat = $this->get_posts_by_category( $cat );
			$this->assertEquals( 0, count( $posts_in_cat ) );

			// Assert that common tag has one posts.
			$posts_in_common_tag = $this->get_posts_by_tag( $common_tag );
			$this->assertEquals( 1, count( $posts_in_common_tag ) );
		} else {
			echo $move_result->get_error_message();
		}
	}
}
