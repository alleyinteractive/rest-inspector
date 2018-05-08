<?php

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['SERVER_NAME']     = '';
$PHP_SELF                   = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

define( 'WP_USE_THEMES', false );

/**
 * Load cores test library.
 */
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load & activate plugin.
 */
function manually_load_plugin() {
	require dirname( __FILE__ ) . '/../rest-inspector.php';
}
tests_add_filter( 'muplugins_loaded', 'manually_load_plugin' );

/**
 * Disable HTTP requests during unit tests.
 *
 * @return WP_Error
 */
function disable_http_requests() {
	return new WP_Error( 'no_reqs_in_unit_tests', __( 'HTTP Requests disabled for unit tests', 'rest-inspector' ) );
}
tests_add_filter( 'pre_http_request', 'disable_http_requests' );

// Include core's bootstrap
require $_tests_dir . '/includes/bootstrap.php';

// Base test class for plugin tests.
require_once 'inc/class-rest-inspector-unittestcase.php';