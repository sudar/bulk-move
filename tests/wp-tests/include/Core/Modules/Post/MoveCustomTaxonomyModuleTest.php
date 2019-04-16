<?php

namespace BulkWP\BulkMove\Core\Modules\Post;

use BulkWP\Tests\WPCore\WPCoreUnitTestCase;

/**
 * Test BulkWP\BulkMove\Core\Modules\Post\MoveCustomTaxonomyModule class.
 */
class MoveCustomTaxonomyModuleTest extends WPCoreUnitTestCase {

	/**
	 * @var \BulkWP\BulkMove\Core\Modules\Post\MoveCustomTaxonomyModule
	 */
	protected $custom_taxonomy_metabox;

	public function setUp() {
		parent::setUp();

		$this->custom_taxonomy_metabox = new MoveCustomTaxonomyModule();

		/* Register custom post type and taxonomy */
		$post_type = 'docs';
		register_post_type( $post_type );
		register_taxonomy( 'docs_category', $post_type );
	}

	public function test_move_posts_from_one_tax_term_to_another_without_overwrite() {
		// Create two categories.
		$cat1 = $this->factory->category->create( array( 'name' => 'cat1' ) );
		$cat2 = $this->factory->category->create( array( 'name' => 'cat2' ) );

		// Create two posts in one category.
		$post1 = $this->factory->post->create( array( 'post_title' => 'post1', 'post_category' => array( $cat1 ) ) );
		$post2 = $this->factory->post->create( array( 'post_title' => 'post2', 'post_category' => array( $cat1 ) ) );

		// Assert the count of posts in each category.
		$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
		$posts_in_cat2 = $this->get_posts_by_category( $cat2 );

		$this->assertEquals( count( $posts_in_cat1 ), 2 );
		$this->assertEquals( count( $posts_in_cat2 ), 0 );

		// call our method.
		$options = array(
			'old_term'   => $cat1,
			'new_term'   => $cat2,
			'taxonomy'   => 'category',
			'overwrite'  => false,
			'post_types' => array( 'post' ),
		);
		$this->custom_taxonomy_metabox->move( $options );

		// Assert that category 1 has two posts.
		$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
		$this->assertEquals( count( $posts_in_cat1 ), 2 );

		// Assert that category 2 has two posts.
		$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
		$this->assertEquals( count( $posts_in_cat2 ), 2 );
	}

	public function test_move_posts_from_one_tax_term_to_another_with_overwrite() {
		// Create two categories.
		$cat3 = $this->factory->category->create( array( 'name' => 'cat3' ) );
		$cat4 = $this->factory->category->create( array( 'name' => 'cat4' ) );

		// Create two posts in one category.
		$post1 = $this->factory->post->create( array( 'post_title' => 'post1', 'post_category' => array( $cat3 ) ) );
		$post2 = $this->factory->post->create( array( 'post_title' => 'post2', 'post_category' => array( $cat3 ) ) );

		// Assert the count of posts in each category.
		$posts_in_cat3 = $this->get_posts_by_category( $cat3 );
		$posts_in_cat4 = $this->get_posts_by_category( $cat4 );

		$this->assertEquals( count( $posts_in_cat3 ), 2 );
		$this->assertEquals( count( $posts_in_cat4 ), 0 );

		// call our method.
		$options = array(
			'old_term'   => $cat3,
			'new_term'   => $cat4,
			'taxonomy'   => 'category',
			'overwrite'  => true,
			'post_types' => array( 'post' ),
		);
		$this->custom_taxonomy_metabox->move( $options );

		// Assert that category 1 has two posts.
		$posts_in_cat3 = $this->get_posts_by_category( $cat3 );
		$this->assertEquals( count( $posts_in_cat3 ), 0 );

		// Assert that category 2 has two posts.
		$posts_in_cat4 = $this->get_posts_by_category( $cat4 );
		$this->assertEquals( count( $posts_in_cat4 ), 2 );
	}

	public function test_move_posts_from_one_custom_tax_term_to_another_without_overwrite() {
		$taxonomy  = 'docs_category';
		$post_type = 'docs';

		// Create two categories.
		$term1 = $this->factory->term->create( array( 'name' => 'term1', 'taxonomy' => $taxonomy ) );
		$term2 = $this->factory->term->create( array( 'name' => 'term2', 'taxonomy' => $taxonomy ) );

		// Create two posts and assign the term.
		$post1 = $this->factory->post->create( array( 'post_title' => 'post1', 'post_type' => $post_type ) );
		$post2 = $this->factory->post->create( array( 'post_title' => 'post2', 'post_type' => $post_type ) );

		wp_set_object_terms( $post1, $term1, $taxonomy );
		wp_set_object_terms( $post2, $term1, $taxonomy );

		// Assert the count of posts in each category.
		$posts_in_term1 = $this->get_posts_by_custom_term( $term1, $taxonomy, $post_type );
		$this->assertEquals( count( $posts_in_term1 ), 2 );

		$posts_in_term2 = $this->get_posts_by_custom_term( $term2, $taxonomy, $post_type );
		$this->assertEquals( count( $posts_in_term2 ), 0 );

		// call our method.
		$options = array(
			'old_term'   => $term1,
			'new_term'   => $term2,
			'taxonomy'   => $taxonomy,
			'overwrite'  => false,
			'post_types' => array( $post_type ),
		);
		$this->custom_taxonomy_metabox->move( $options );

		// Assert that category 1 has two posts.
		$posts_in_term1 = $this->get_posts_by_custom_term( $term1, $taxonomy, $post_type );
		$this->assertEquals( count( $posts_in_term1 ), 2 );

		// Assert that category 2 has two posts.
		$posts_in_term2 = $this->get_posts_by_custom_term( $term2, $taxonomy, $post_type );
		$this->assertEquals( count( $posts_in_term2 ), 2 );
	}

	public function test_move_posts_from_one_custom_tax_term_to_another_with_overwrite() {
		$taxonomy  = 'docs_category';
		$post_type = 'docs';

		// Create two categories.
		$term3 = $this->factory->term->create( array( 'name' => 'term3', 'taxonomy' => $taxonomy ) );
		$term4 = $this->factory->term->create( array( 'name' => 'term4', 'taxonomy' => $taxonomy ) );

		// Create two posts and assign the term.
		$post3 = $this->factory->post->create( array( 'post_title' => 'post3', 'post_type' => $post_type ) );
		$post4 = $this->factory->post->create( array( 'post_title' => 'post4', 'post_type' => $post_type ) );

		wp_set_object_terms( $post3, $term3, $taxonomy );
		wp_set_object_terms( $post4, $term3, $taxonomy );

		// Assert the count of posts in each category.
		$posts_in_term3 = $this->get_posts_by_custom_term( $term3, $taxonomy, $post_type );
		$this->assertEquals( count( $posts_in_term3 ), 2 );

		$posts_in_term4 = $this->get_posts_by_custom_term( $term4, $taxonomy, $post_type );
		$this->assertEquals( count( $posts_in_term4 ), 0 );

		// call our method.
		$options = array(
			'old_term'   => $term3,
			'new_term'   => $term4,
			'taxonomy'   => $taxonomy,
			'overwrite'  => true,
			'post_types' => array( $post_type ),
		);
		$this->custom_taxonomy_metabox->move( $options );

		// Assert that category 1 has two posts.
		$posts_in_term3 = $this->get_posts_by_custom_term( $term3, $taxonomy, $post_type );
		$this->assertEquals( count( $posts_in_term3 ), 0 );

		// Assert that category 2 has two posts.
		$posts_in_term4 = $this->get_posts_by_custom_term( $term4, $taxonomy, $post_type );
		$this->assertEquals( count( $posts_in_term4 ), 2 );
	}

	public function test_remove_term_with_overwrite() {
		// Create two categories.
		$cat1 = $this->factory->category->create( array( 'name' => 'cat1' ) );
		$cat2 = $this->factory->category->create( array( 'name' => 'cat2' ) );

		// Create one post in two categories.
		$post1 = $this->factory->post->create( array( 'post_title'    => 'post1',
		                                              'post_category' => array( $cat1, $cat2 )
		) );

		// Assert the count of posts in each category.
		$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
		$posts_in_cat2 = $this->get_posts_by_category( $cat2 );

		$this->assertEquals( count( $posts_in_cat1 ), 1 );
		$this->assertEquals( count( $posts_in_cat2 ), 1 );

		// call our method.
		$options = array(
			'old_term'   => $cat1,
			'new_term'   => -1,
			'taxonomy'   => 'category',
			'overwrite'  => true,
			'post_types' => array( 'post' ),
		);
		$this->custom_taxonomy_metabox->move( $options );

		// Assert that category 1 has no post.
		$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
		$this->assertEquals( count( $posts_in_cat1 ), 0 );

		// Assert that category 2 has one post.
		$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
		$this->assertEquals( count( $posts_in_cat2 ), 1 );
	}

	public function test_remove_term_without_overwrite() {
		// Create two categories.
		$cat1 = $this->factory->category->create( array( 'name' => 'cat1' ) );
		$cat2 = $this->factory->category->create( array( 'name' => 'cat2' ) );

		// Create one post in two categories.
		$post1 = $this->factory->post->create( array( 'post_title'    => 'post1',
		                                              'post_category' => array( $cat1, $cat2 )
		) );

		// Assert the count of posts in each category.
		$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
		$posts_in_cat2 = $this->get_posts_by_category( $cat2 );

		$this->assertEquals( count( $posts_in_cat1 ), 1 );
		$this->assertEquals( count( $posts_in_cat2 ), 1 );

		// call our method.
		$options = array(
			'old_term'   => $cat1,
			'new_term'   => -1,
			'taxonomy'   => 'category',
			'overwrite'  => false,
			'post_types' => array( 'post' ),
		);
		$this->custom_taxonomy_metabox->move( $options );

		// Assert that category 1 has no post.
		$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
		$this->assertEquals( count( $posts_in_cat1 ), 0 );

		// Assert that category 2 has one post.
		$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
		$this->assertEquals( count( $posts_in_cat2 ), 1 );
	}
}