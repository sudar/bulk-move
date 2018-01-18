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

		$post_cat = $this->factory->post->create( array( 'post_title'    => 'post_cat',
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
		$this->category_to_tag_metabox->move( $options );

		// Assert that tag 1 has two posts.
		$posts_in_tag = $this->get_posts_by_tag( $tag );
		$this->assertEquals( 2, count( $posts_in_tag ) );

		// Assert that category has no posts.
		$posts_in_cat = $this->get_posts_by_category( $cat );
		$this->assertEquals( 0, count( $posts_in_cat ) );
	}
}
