<?php
/**
 * REST Inspector WordPress Plugin.
 *
 * @link              https://alley.co
 * @since             1.0.0
 * @package           Rest_Inspector
 *
 * @wordpress-plugin
 * Plugin Name:       REST Inspector
 * Plugin URI:        http://wordpress.org/extend/plugins/rest-inspector
 * Description:       See WP REST API endpoint data for posts, terms, and users.
 * Version:           1.0.0
 * Author:            Alley, Griffen Fargo, James Burke
 * Author URI:        https://Alley.co
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rest-inspector
 * Domain Path:       /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'REST_INSPECTOR_VERSION', '0.1.0' );
define( 'REST_INSPECTOR_ROOT', plugin_dir_path( __FILE__ ) );
define( 'REST_INSPECTOR_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load plugin dependencies.
 */

// phpcs:disable WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant -- Constants defined above via core helpers.
require REST_INSPECTOR_ROOT . '/inc/traits/trait-singleton.php';

require REST_INSPECTOR_ROOT . '/inc/helpers.php';

require REST_INSPECTOR_ROOT . '/inc/class-rest-inspector.php';

require REST_INSPECTOR_ROOT . '/inc/class-wp-list-table.php';

require REST_INSPECTOR_ROOT . '/inc/class-meta-box.php';
// phpcs:enable WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant

/**
 * Helper for accessing main plugin class.
 *
 * @return REST_Inspector Instance of main plugin class
 */
function rest_inspector() {
	return REST_Inspector::instance();
}
add_action( 'plugins_loaded', 'rest_inspector' );
