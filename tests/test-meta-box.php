<?php
/**
 * Tests meta box integration.
 */

/**
 * Class Tests_Meta_Box
 */
class Tests_Meta_Box extends REST_Inspector\UnitTestBase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_meta_box_render() {
		REST_Inspector::$object_id = 0;
		REST_Inspector::$type = 'post';
		REST_Inspector::$route_uri = $this->plugin_instance->get_the_route();
		
		ob_start();
		\REST_Inspector\Meta_Box()->render();
		$output = ob_get_clean();

		$this->assertNotEmpty( $output );

		$this->assertContains( '<strong>Server Response:</strong>', $output );
		$this->assertContains( '<strong>Route URI:</strong>', $output );
	}

}