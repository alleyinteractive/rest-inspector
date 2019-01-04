<?php
/**
 * Core Class for REST Inspector Plugin
 *
 * @link       https://Alley.co
 *
 * @package    Rest_Inspector
 * @subpackage Rest_Inspector/inc
 */

/**
 * Class REST_Inspector
 */
class REST_Inspector {
	use \REST_Inspector\Singleton;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @var string $name The string used to uniquely identify this plugin.
	 */
	public $name = 'rest-inspector';

	/**
	 * Slug for parent page for admin submenu.
	 *
	 * @var string $parent_slug
	 */
	public $parent_slug = 'tools.php';

	/**
	 * User capability required to view submenu page
	 *
	 * @var string $view_cap
	 */
	public $view_cap = 'manage_options';

	/**
	 * Instance of the WP Rest Server.
	 *
	 * @var WP_REST_Server REST server instance.
	 */
	public $server;

	/**
	 * The current version of the plugin.
	 *
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The ID of the object whose REST route is being rendered.
	 *
	 * @var int
	 */
	public static $object_id;

	/**
	 * URI for current rest route.
	 *
	 * @var int
	 */
	public static $route_uri;

	/**
	 * The type of rest response being displayed. Variation of `post`, `term`, 'comment', or `user`.
	 *
	 * @var string
	 */
	public static $type;

	/**
	 * Total number of REST endpoints found.
	 *
	 * @var int
	 */
	public static $total_endpoints = 0;

	/**
	 * Number of displayed REST endpoints after filtering.
	 *
	 * @var int
	 */
	public static $visible_endpoints = 0;

	/**
	 * List of available callback functions used for filtering.
	 *
	 * @var array
	 */
	public static $endpoint_callbacks = [];

	/**
	 * Array of HTTP methods provided by REST server.
	 *
	 * @var array
	 */
	public static $server_methods = [];

	/**
	 * Replacement constructor for hooking actions
	 */
	public function setup() {
		if ( ! is_admin() ) {
			return;
		}

		// Setup version.
		$this->version = defined( 'REST_INSPECTOR_VERSION' ) ? REST_INSPECTOR_VERSION : '1.0.0';

		add_action( 'current_screen', [ $this, 'set_type' ] );

		add_action( 'rest_api_init', [ $this, 'get_server' ] );

		// Setup i18n.
		add_action( 'init', [ $this, 'set_locale' ] );

		// Load admin scripts & styles.
		add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_dependencies' ] );

		// Add our sub-menu page to wp admin.
		add_action( 'admin_menu', [ $this, 'register_admin_page' ] );
	}

	/**
	 * Add Plugin Submenu page to WordPress.
	 */
	public function register_admin_page() {
		add_submenu_page(
			$this->parent_slug,
			__( 'REST API Inspector', 'rest-inspector' ),
			__( 'REST Inspector', 'rest-inspector' ),
			$this->view_cap,
			$this->name,
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Enqueues admin dependencies for this plugin.
	 */
	public function load_admin_dependencies() {
		// Load RenderJSON lib for collapsible json output.
		wp_enqueue_script(
			'renderJSON-js',
			REST_INSPECTOR_URL . 'assets/vendor/renderjson.js',
			array(),
			'1.0',
			true
		);

		// Load Plugin JS.
		wp_enqueue_script(
			$this->name . '-js',
			REST_INSPECTOR_URL . 'assets/js/rest-inspector-admin.js',
			array( 'jquery', 'renderJSON-js' ),
			$this->version,
			true
		);

		// Load Plugin Styles.
		wp_enqueue_style(
			$this->name . '-css',
			REST_INSPECTOR_URL . 'assets/css/rest-inspector-admin.css',
			array(),
			$this->version
		);
	}

	/**
	 * Define the locale for internationalization.
	 */
	public function set_locale() {
		load_plugin_textdomain(
			'rest-inspector',
			false,
			REST_INSPECTOR_ROOT . '/lang/'
		);
	}

	/**
	 * Setup static type for current page.
	 *
	 * Used to determine current route in `get_the_route` method and when creating the meta box.
	 *
	 * @uses get_post_type()
	 * @uses get_current_screen()
	 */
	public function set_type() {
		self::$type = get_post_type();

		// Use current screen ID as fallback if post type is unavailable.
		if ( empty( self::$type ) ) {
			self::$type = get_current_screen()->id;
		}
	}

	/**
	 * Retrieve the version number.
	 *
	 * @return string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve instance of REST server.
	 *
	 * @return WP_REST_Server REST server instance.
	 */
	public function get_server() {
		if ( empty( $this->server ) ) {
			// Cache REST server instance.
			$this->server = rest_get_server();
		}

		return $this->server;
	}


	/**
	 * Generate Rest Response using the $route_uri static var.
	 *
	 * @return WP_REST_Response
	 */
	public function get_rest_response() {
		// Create REST request using static class values.
		$request  = new WP_REST_Request( 'GET', self::$route_uri );
		$server   = rest_inspector()->get_server();
		$response = $server->dispatch( $request );

		return $response;
	}

	/**
	 * Determine REST route URI key from current context.
	 *
	 * @return string|null
	 */
	public function get_the_route() {
		// Determine REST API base for current post.
		switch ( self::$type ) {
			case 'user':
				$object_id = self::$object_id;
				$wp_object = get_user_by( 'id', $object_id );
				$rest_base = 'users';
				break;
			case 'comment':
				$object_id = self::$object_id;
				$wp_object = get_comment( $object_id );
				$rest_base = 'comments';
				break;
			case 'term':
				$object_id = self::$object_id;
				$wp_object = isset( $_GET['taxonomy'] ) ? get_taxonomy( wp_unslash( sanitize_text_field( $_GET['taxonomy'] ) ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
				$rest_base = ! empty( $wp_object->rest_base ) ? $wp_object->rest_base : $wp_object->name;
				break;
			default:
				$object_id = self::$object_id;
				$wp_object = get_post_type_object( self::$type );
				$rest_base = ! empty( $wp_object->rest_base ) ? $wp_object->rest_base : $wp_object->name;
		}

		/**
		 * Filter default REST controller class.
		 *
		 * @var string
		 */
		$default_controller = apply_filters( 'rest_inspector_default_controller_class', 'WP_REST_Controller' );

		// Extract controller class from object, otherwise use default.
		$rest_controller_class = ! empty( $wp_object->rest_controller_class ) ? $wp_object->rest_controller_class : $default_controller;

		/**
		 * Filter available REST controller classes.  Defaults to classes available in core.
		 *
		 * @var array $controllers
		 */
		$controllers = apply_filters(
			'rest_inspector_registered_rest_controllers',
			[
				'WP_REST_Controller',
				'WP_REST_Posts_Controller',
				'WP_REST_Terms_Controller',
				'WP_REST_Users_Controller',
				'WP_REST_Comments_Controller',
				'WP_REST_Revisions_Controller',
				'WP_REST_Settings_Controller',
				'WP_REST_Taxonomies_Controller',
				'WP_REST_Attachments_Controller',
				'WP_REST_Post_Types_Controller',
			],
			$wp_object
		);

		// Exit if we are working with unknown controller.
		if ( ! in_array( $rest_controller_class, $controllers, true ) ) {
			return null;
		}

		/**
		 * Filter REST namespace.
		 *
		 * @var string $namespace
		 */
		$namespace = apply_filters( 'rest_inspector_default_namespace', 'wp/v2' );

		// Build and return route.
		return '/' . $namespace . '/' . $rest_base . '/' . $object_id;
	}

	/**
	 * Display Admin Page.
	 */
	public function render_admin_page() {
		if ( ! current_user_can( $this->view_cap ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'rest-inspector' ) );
		}

		include REST_INSPECTOR_ROOT . '/templates/rest-inspector-admin-display.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
	}
}
