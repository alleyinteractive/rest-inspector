<?php

class Tests_REST_Inspector extends REST_Inspector_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_plugin_instance() {
		$this->assertClassHasStaticAttribute( 'instance', 'REST_Inspector' );
		$this->assertClassHasStaticAttribute( 'instance', 'REST_Inspector_Meta_Box' );
	}

	public function test_constants() {
		// Plugin Folder URL
		$path = str_replace( 'tests/', '', plugin_dir_url( __FILE__ ) );
		$this->assertSame( REST_INSPECTOR_URL, $path );

		// Plugin Folder Path
		$path = str_replace( 'tests/', '', plugin_dir_path( __FILE__ ) );
		$path = substr( $path, 0, - 1 );
		$this->assertSame( REST_INSPECTOR_ROOT, $path );

		// Plugin Root File
		$path = str_replace( 'tests/', '', plugin_dir_path( __FILE__ ) );
		$this->assertSame( REST_INSPECTOR_FILE_PATH, $path . 'rest-inspector.php' );

		// Plugin Version
		$this->assertNotEmpty( REST_INSPECTOR_VERSION );
	}

	public function test_dependencies() {
		// Check enqueued assets.
		$this->assertFileExists( REST_INSPECTOR_ROOT . '/assets/css/rest-inspector-admin.css' );
		$this->assertFileExists( REST_INSPECTOR_ROOT . '/assets/js/rest-inspector-admin.js' );
		$this->assertFileExists( REST_INSPECTOR_ROOT . '/assets/vendor/renderjson.js' );
	}

	public function test_get_rest_server() {
		$REST_server = $this->plugin_instance->get_server();

		$this->assertNotWPError( $REST_server );

		$this->assertEquals( true, $REST_server instanceof WP_REST_Server );
	}

	public function test_get_rest_response() {
		REST_Inspector::$route_uri = '/wp/v2/posts/0';
		$test_response = $this->plugin_instance->get_rest_response();

		$this->assertNotWPError( $test_response );

		$this->assertEquals( true, $test_response instanceof WP_REST_Response );
	}
}