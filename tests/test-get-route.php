<?php
/**
 * Tests for REST_Inspector::get_the_route() method.
 */

/**
 * Class Tests_Get_Route
 */
class Tests_Get_Route extends REST_Inspector\UnitTestBase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_get_post_route() {
		REST_Inspector::$object_id = 0;
		REST_Inspector::$type = 'post';
		$post_route = $this->plugin_instance->get_the_route();

		$this->assertSame( '/wp/v2/posts/0', $post_route );
	}

	public function test_get_page_route() {
		REST_Inspector::$object_id = 0;
		REST_Inspector::$type = 'page';
		$post_route = $this->plugin_instance->get_the_route();

		$this->assertSame( '/wp/v2/pages/0', $post_route );
	}

	public function test_get_term_routes() {
		// Test Post Tags
		REST_Inspector::$object_id = 0;
		REST_Inspector::$type = 'term';
		$_GET['taxonomy'] = 'post_tag';
		$post_route = $this->plugin_instance->get_the_route();

		$this->assertSame( '/wp/v2/tags/0', $post_route );

		// Test Category Taxonomy
		REST_Inspector::$object_id = 0;
		REST_Inspector::$type = 'term';
		$_GET['taxonomy'] = 'category';
		$post_route = $this->plugin_instance->get_the_route();

		$this->assertSame( '/wp/v2/categories/0', $post_route );
	}

	public function test_get_comment_route() {
		REST_Inspector::$object_id = 0;
		REST_Inspector::$type = 'comment';
		$post_route = $this->plugin_instance->get_the_route();

		$this->assertSame( '/wp/v2/comments/0', $post_route );
	}

	public function test_get_user_route() {
		REST_Inspector::$object_id = 0;
		REST_Inspector::$type = 'user';
		$post_route = $this->plugin_instance->get_the_route();

		$this->assertSame( '/wp/v2/users/0', $post_route );
	}
}