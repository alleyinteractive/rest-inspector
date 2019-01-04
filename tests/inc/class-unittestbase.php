<?php
/**
 * Base Test Class for REST Inspector Plugin.
 */

namespace REST_Inspector;

/**
 * Class REST_Inspector_UnitTest
 */
class UnitTestBase extends \WP_UnitTestCase {
	// Instance of REST Inspector singleton.
	protected $plugin_instance;

	public function setUp() {
		parent::setUp();

		// Setup admin
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$user = wp_set_current_user( $user_id );

		$this->plugin_instance = rest_inspector();
	}

	public function tearDown() {
		parent::tearDown();
	}
}