<?php
/**
 * Trait file for Singletons.
 *
 * @link       https://Alley.co
 *
 * @package    Rest_Inspector
 * @subpackage Rest_Inspector/inc/traits
 */
trait Singleton {
	/**
	 * Existing instance.
	 *
	 * @var array
	 */
	protected static $instance;
	public function __clone() {
		wp_die( "Please don't __clone" );
	}
	public function __wakeup() {
		wp_die( "Please don't __wakeup" );
	}
	/**
	 * Get class instance.
	 *
	 * @return object
	 */
	public static function instance() {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new static();
			static::$instance->setup();
		}
		return static::$instance;
	}
	/**
	 * Setup the singleton.
	 */
	public function setup() {
		// Silence
	}
}