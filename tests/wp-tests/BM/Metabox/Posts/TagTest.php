<?php

/**
 * Test BM_Metabox_Posts_Tag class.
 */
class TagTest extends BM_TestCase {

	/**
	 * @var \BM_Metabox_Posts_Tag
	 */
	protected $tag_metabox;

	public function setUp() {
		parent::setUp();

		$this->tag_metabox = new \BM_Metabox_Posts_Tag();
	}

	/**
	 * Test basic case of moving tags.
	 */
	public function test_move_posts_from_one_tag_to_another() {
		// Create two tags.
		$tag1 = $this->factory->tag->create( array( 'name' => 'tag1' ) );
		$tag2 = $this->factory->tag->create( array( 'name' => 'tag2' ) );

		// Create one post in each tag.
		$post1 = $this->factory->post->create( array( 'post_title' => 'post1' ) );
		wp_set_post_tags( $post1, 'tag1' );

		$post2 = $this->factory->post->create( array( 'post_title' => 'post2' ) );
		wp_set_post_tags( $post2, 'tag2' );

		// Assert that each tag has one post.
		$posts_in_tag1 = $this->get_posts_by_tag( $tag1 );
		$posts_in_tag2 = $this->get_posts_by_tag( $tag2 );

		$this->assertEquals( 1, count( $posts_in_tag1 ) );
		$this->assertEquals( 1, count( $posts_in_tag2 ) );

		// call our method.
		$options = array(
			'old_tag'   => $tag1,
			'new_tag'   => $tag2,
			'overwrite' => true,
		);
		$this->tag_metabox->move( $options );

		// Assert that tag 1 has no posts.
		$posts_in_tag1 = $this->get_posts_by_tag( $tag1 );
		$this->assertEquals( 0, count( $posts_in_tag1 ) );

		// Assert that tag 2 has two posts.
		$posts_in_tag2 = $this->get_posts_by_tag( $tag2 );
		$this->assertEquals( 2, count( $posts_in_tag2 ) );
	}

	/**
	 * Test moving posts from one tag to another with overwrite.
	 */
	public function test_move_posts_from_one_tag_to_another_with_overwrite() {
		// Create two tags and a common tag.
		$tag1 = $this->factory->tag->create( array( 'name' => 'tag1' ) );
		$tag2 = $this->factory->tag->create( array( 'name' => 'tag2' ) );
		$common_tag = $this->factory->tag->create( array( 'name' => 'common_tag' ) );
		
		// Create one post in each tag.
		// The first post will also have the common tag.
		$post1 = $this->factory->post->create( array( 'post_title' => 'post1' ) );
		wp_set_post_tags( $post1, array( 'tag1', 'common_tag' ) );
		
		$post2 = $this->factory->post->create( array( 'post_title' => 'post2' ) );
		wp_set_post_tags( $post2, 'tag2' );
		
		// Assert that each tag has one post.
		$posts_in_tag1 = $this->get_posts_by_tag( $tag1 );
		$posts_in_tag2 = $this->get_posts_by_tag( $tag2 );
		$posts_in_common_tag = $this->get_posts_by_tag( $common_tag );
		
		$this->assertEquals( 1, count( $posts_in_tag1 ) );
		$this->assertEquals( 1, count( $posts_in_tag2 ) );
		$this->assertEquals( 1, count( $posts_in_common_tag ) );
		
		// call our method.
		$options = array(
		    'old_tag'   => $tag1,
		    'new_tag'   => $tag2,
		    'overwrite' => true,
		);
		$this->tag_metabox->move( $options );
		
		// Assert that tag 1 has no posts.
		$posts_in_tag1 = $this->get_posts_by_tag( $tag1 );
		$this->assertEquals( 0, count( $posts_in_tag1 ) );
		
		// Assert that common tag has 0 posts.
		$posts_in_common_tag = $this->get_posts_by_tag( $common_tag );
		$this->assertEquals( 0, count( $posts_in_common_tag ) );
		
		// Assert that tag 2 has two posts.
		$posts_in_tag2 = $this->get_posts_by_tag( $tag2 );
		$this->assertEquals( 2, count( $posts_in_tag2 ) );
	}

	/**
	 * Test Moving tag without overwrite.
	 */
	public function test_move_posts_from_one_tag_to_another_without_overwrite() {
        // Create two tags and a common tag.
        $tag1 = $this->factory->tag->create( array( 'name' => 'tag1' ) );
        $tag2 = $this->factory->tag->create( array( 'name' => 'tag2' ) );
        $common_tag = $this->factory->tag->create( array( 'name' => 'common_tag' ) );

        // Create one post in each tag.
        // The first post will also have the common tag.
        $post1 = $this->factory->post->create( array( 'post_title' => 'post1' ) );
        wp_set_post_tags( $post1, array( 'tag1', 'common_tag' ) );

        $post2 = $this->factory->post->create( array( 'post_title' => 'post2' ) );
        wp_set_post_tags( $post2, 'tag2' );

        // Assert that each tag has one post.
        $posts_in_tag1 = $this->get_posts_by_tag( $tag1 );
        $posts_in_tag2 = $this->get_posts_by_tag( $tag2 );
        $posts_in_common_tag = $this->get_posts_by_tag( $common_tag );

        $this->assertEquals( 1, count( $posts_in_tag1 ) );
        $this->assertEquals( 1, count( $posts_in_tag2 ) );
        $this->assertEquals( 1, count( $posts_in_common_tag ) );

        // call our method.
        $options = array(
            'old_tag'   => $tag1,
            'new_tag'   => $tag2,
            'overwrite' => false,
        );
        $this->tag_metabox->move( $options );

        // Assert that tag 1 has no posts.
        $posts_in_tag1 = $this->get_posts_by_tag( $tag1 );
        $this->assertEquals( 0, count( $posts_in_tag1 ) );

        // Assert that common tag has one posts.
        $posts_in_common_tag = $this->get_posts_by_tag( $common_tag );
        $this->assertEquals( 1, count( $posts_in_common_tag ) );

        // Assert that tag 2 has two posts.
        $posts_in_tag2 = $this->get_posts_by_tag( $tag2 );
        $this->assertEquals( 2, count( $posts_in_tag2 ) );
	}

    /**
     * Test basic case of removing tags.
     */
    public function test_remove_posts_from_tag() {
        // Create two tags.
        $tag1 = $this->factory->tag->create( array( 'name' => 'tag1' ) );
        $tag2 = $this->factory->tag->create( array( 'name' => 'tag2' ) );

        // Create one post in each tag.
        $post1 = $this->factory->post->create( array( 'post_title' => 'post1' ) );
        wp_set_post_tags( $post1, 'tag1' );

        $post2 = $this->factory->post->create( array( 'post_title' => 'post2' ) );
        wp_set_post_tags( $post2, 'tag2' );

        // Assert that each tag has one post.
        $posts_in_tag1 = $this->get_posts_by_tag( $tag1 );
        $posts_in_tag2 = $this->get_posts_by_tag( $tag2 );

        $this->assertEquals( 1, count( $posts_in_tag1 ) );
        $this->assertEquals( 1, count( $posts_in_tag2 ) );

        // call our method.
        $options = array(
            'old_tag'   => $tag1,
            'new_tag'   => -1,
            'overwrite' => true,
        );
        $this->tag_metabox->move( $options );

        // Assert that tag 1 has no posts.
        $posts_in_tag1 = $this->get_posts_by_tag( $tag1 );
        $this->assertEquals( 0, count( $posts_in_tag1 ) );

        // Assert that tag 2 has one posts.
        $posts_in_tag2 = $this->get_posts_by_tag( $tag2 );
        $this->assertEquals( 1, count( $posts_in_tag2 ) );
    }

    /**
     * Test removing posts from tag with overwrite.
     */
    public function test_remove_posts_from_tag_with_overwrite() {
        // Create two tags and a common tag.
        $tag1 = $this->factory->tag->create( array( 'name' => 'tag1' ) );
        $tag2 = $this->factory->tag->create( array( 'name' => 'tag2' ) );
        $common_tag = $this->factory->tag->create( array( 'name' => 'common_tag' ) );

        // Create one post in each tag.
        // The first post will also have the common tag.
        $post1 = $this->factory->post->create( array( 'post_title' => 'post1' ) );
        wp_set_post_tags( $post1, array( 'tag1', 'common_tag' ) );

        $post2 = $this->factory->post->create( array( 'post_title' => 'post2' ) );
        wp_set_post_tags( $post2, 'tag2' );

        // Assert that each tag has one post.
        $posts_in_tag1 = $this->get_posts_by_tag( $tag1 );
        $posts_in_tag2 = $this->get_posts_by_tag( $tag2 );
        $posts_in_common_tag = $this->get_posts_by_tag( $common_tag );

        $this->assertEquals( 1, count( $posts_in_tag1 ) );
        $this->assertEquals( 1, count( $posts_in_tag2 ) );
        $this->assertEquals( 1, count( $posts_in_common_tag ) );

        // call our method.
        $options = array(
            'old_tag'   => $tag1,
            'new_tag'   => -1,
            'overwrite' => true,
        );
        $this->tag_metabox->move( $options );

        // Assert that tag 1 has no posts.
        $posts_in_tag1 = $this->get_posts_by_tag( $tag1 );
        $this->assertEquals( 0, count( $posts_in_tag1 ) );

        // Assert that common tag has 0 posts.
        $posts_in_common_tag = $this->get_posts_by_tag( $common_tag );
        $this->assertEquals( 0, count( $posts_in_common_tag ) );

        // Assert that tag 2 has one posts.
        $posts_in_tag2 = $this->get_posts_by_tag( $tag2 );
        $this->assertEquals( 1, count( $posts_in_tag2 ) );
    }

    /**
     * Test removing posts from tag without overwrite.
     */
    public function test_remove_posts_from_tag_without_overwrite() {
        // Create two tags and a common tag.
        $tag1 = $this->factory->tag->create( array( 'name' => 'tag1' ) );
        $tag2 = $this->factory->tag->create( array( 'name' => 'tag2' ) );
        $common_tag = $this->factory->tag->create( array( 'name' => 'common_tag' ) );

        // Create one post in each tag.
        // The first post will also have the common tag.
        $post1 = $this->factory->post->create( array( 'post_title' => 'post1' ) );
        wp_set_post_tags( $post1, array( 'tag1', 'common_tag' ) );

        $post2 = $this->factory->post->create( array( 'post_title' => 'post2' ) );
        wp_set_post_tags( $post2, 'tag2' );

        // Assert that each tag has one post.
        $posts_in_tag1 = $this->get_posts_by_tag( $tag1 );
        $posts_in_tag2 = $this->get_posts_by_tag( $tag2 );
        $posts_in_common_tag = $this->get_posts_by_tag( $common_tag );

        $this->assertEquals( 1, count( $posts_in_tag1 ) );
        $this->assertEquals( 1, count( $posts_in_tag2 ) );
        $this->assertEquals( 1, count( $posts_in_common_tag ) );

        // call our method.
        $options = array(
            'old_tag'   => $tag1,
            'new_tag'   => -1,
            'overwrite' => false,
        );
        $this->tag_metabox->move( $options );

        // Assert that tag 1 has no posts.
        $posts_in_tag1 = $this->get_posts_by_tag( $tag1 );
        $this->assertEquals( 0, count( $posts_in_tag1 ) );

        // Assert that common tag has one posts.
        $posts_in_common_tag = $this->get_posts_by_tag( $common_tag );
        $this->assertEquals( 1, count( $posts_in_common_tag ) );

        // Assert that tag 2 has one posts.
        $posts_in_tag2 = $this->get_posts_by_tag( $tag2 );
        $this->assertEquals( 1, count( $posts_in_tag2 ) );
    }
}
