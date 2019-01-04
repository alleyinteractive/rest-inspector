<?php
/**
 * Trait file for Singletons.
 *
 * @link       https://Alley.co
 *
 * @package    Rest_Inspector
 * @subpackage Rest_Inspector/inc/traits
 */

namespace REST_Inspector;

trait Singleton {
	/**
	 * Existing instance.
	 *
	 * @var array
	 */
	protected static $instance;

	/**
	 * Prevents cloning for singletons.
	 */
	public function __clone() {
		wp_die( "Please don't __clone" );
	}

	/**
	 * Prevents wakeup for singletons.
	 */
	public function __wakeup() {
		wp_die( "Please don't __wakeup" );
	}

	/**
	 * Get class instance.
	 *
	 * @return self
	 */
	final public static function instance() {
		// phpcs:disable WordPressVIPMinimum.Variables.VariableAnalysis.StaticOutsideClass
		return isset( static::$instance )
			? static::$instance
			: static::$instance = new static();
		// phpcs:enable WordPressVIPMinimum.Variables.VariableAnalysis.StaticOutsideClass
	}

	/**
	 * Singleton trait constructor.
	 */
	final private function __construct() {
		$this->setup();
	}

	/**
	 * Setup the singleton.
	 */
	protected function setup() {}
}
