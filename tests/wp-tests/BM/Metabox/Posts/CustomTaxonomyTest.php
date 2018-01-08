<?php

class CustomTaxonomyTest extends \BM_TestCase {

	/**
	 * @var \BM_Metabox_Posts_CustomTaxonomy
	 */
	protected $custom_taxonomy_metabox;

	public function setUp() {
		parent::setUp();

		$this->custom_taxonomy_metabox = new \BM_Metabox_Posts_CustomTaxonomy();
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
			'old_term'  => $cat1,
			'new_term'  => $cat2,
			'taxonomy'  => 'category',
			'overwrite' => false,
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
			'old_term'  => $cat1,
			'new_term'  => $cat2,
			'taxonomy'  => 'category',
			'overwrite' => true,
			'post_types' => array( 'post' ),
		);
		$this->custom_taxonomy_metabox->move( $options );

		// Assert that category 1 has two posts.
		$posts_in_cat1 = $this->get_posts_by_category( $cat1 );
		$this->assertEquals( count( $posts_in_cat1 ), 0 );

		// Assert that category 2 has two posts.
		$posts_in_cat2 = $this->get_posts_by_category( $cat2 );
		$this->assertEquals( count( $posts_in_cat2 ), 2 );
	}

	public function test_move_posts_from_one_custom_tax_term_to_another_without_overwrite() {
		$taxonomy = 'docs';

		// TODO: Register Taxonomy

		/*
		// Create two categories.
		$term1 = $this->factory->term->create_and_get( array( 'name' => 'term1', 'taxonomy' => $taxonomy ) );
		$term2 = $this->factory->term->create_and_get( array( 'name' => 'term2', 'taxonomy' => $taxonomy ) );

		// Create two posts in one category.
		$post1 = $this->factory->post->create( array( 'post_title' => 'post1', 'post_category' => array( $term1 ) ) );
		$post2 = $this->factory->post->create( array( 'post_title' => 'post2', 'post_category' => array( $term1 ) ) );


		// Assert the count of posts in each category.
		$posts_in_term1 = $this->get_posts_by_custom_term( $term1, $taxonomy );
		$this->assertEquals( count( $posts_in_term1 ), 2 );
		*/
	}
}