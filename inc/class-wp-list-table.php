<?php
/**
 * Rest Inspector List Table
 *
 * Extended List Table Class for Displaying Rest API Information
 *
 * @link       https://Alley.co
 *
 * @package    Rest_Inspector
 * @subpackage Rest_Inspector/inc
 */

namespace REST_Inspector;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . '/wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class WP_List_Table
 */
class WP_List_Table extends \WP_List_Table {
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
	 * Construct the list table
	 */
	function __construct() {
		parent::__construct( [
			'singular' => __( 'Rest Inspector', 'rest-inspector' ),
		] );
	}

	/**
     * Filter displayed endpoints via search query.
     *
	 * @param array $routes
	 *
	 * @return array Modified list of endpoints
	 */
	function filter_by_search( $routes ) {
		if ( empty( $_GET['s'] ) ) {
		    return $routes;
		}

		$route_search = strtolower( esc_html( sanitize_text_field( $_GET['s'] ) ) );

		// Filter based on search.
		return array_filter( $routes, function ( $route_uri ) use ( $route_search ) {
			return stripos( strtolower( $route_uri ), $route_search ) !== false;
		}, ARRAY_FILTER_USE_KEY );
    }

	/**
     * Filter displayed endpoints by HTTP method.
     *
	 * @param array $routes
	 *
	 * @return array Modified list of endpoints
	 */
	function filter_by_method( $routes ) {
	    if ( empty( $_GET['method'] ) || 'all' === $_GET['method'] ) {
	        return $routes;
        }

		// Filter based on match or source if necessary
		foreach ( $routes as $route_uri => $endpoints ) {
			$routes[ $route_uri ] = array_filter( $endpoints, function ( $endpoint ) {
				return in_array( $_GET['method'], array_keys( $endpoint['methods'] ) );
			} );
		}

		// filter return to remove any empty leftovers.
		return array_filter( $routes );
	}

	/**
     * Filter displayed endpoints by callback.
     *
	 * @param array $routes
	 *
	 * @return array Modified list of endpoints
	 */
	function filter_by_callback( $routes ) {
	    if ( empty( $_GET['callback'] ) || 'all' === $_GET['callback'] ) {
	        return $routes;
        }

		// Filter based on match or source if necessary
		foreach ( $routes as $route_uri => $endpoints ) {
			$routes[ $route_uri ] = array_filter( $endpoints, function ( $endpoint ) {
			    // Removing double forward slash created when passing classname with namespace as GET param.
				return str_replace('\\\\', '\\', $_GET['callback'] ) === get_class( $endpoint['callback'][0] );
			} );
		}

		// filter return to remove any empty leftovers.
		return array_filter( $routes );
	}

	/**
	 * Load all of the matching rewrite rules into our list table
	 */
	function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = [];
		$sortable              = [];
		$this->_column_headers = [ $columns, $hidden, $sortable ];

		// Grab routes from REST server instance.
		$server = REST_Inspector()->get_server();
		$routes = $server->get_routes();

		// Determine what callbacks we can filter by.
		$this::$endpoint_callbacks = array_unique( array_reduce( $routes, function( $controllers, $uri ) {
		    foreach ( $uri as $endpoint ) {
			    $controllers[] = get_class( $endpoint['callback'][0] );
            }
		    return $controllers;
		}, [] ) );

		// Calculate total number of REST endpoints available.
		$this::$total_endpoints = array_reduce( $routes, function( $total, $uri ) {
		    return $total + count( $uri );
        }, 0 );

		$routes = $this->filter_by_method( $routes );
		$routes = $this->filter_by_callback( $routes );
		$routes = $this->filter_by_search( $routes );

		// Calculate number of visible endpoints after filters.
		$this::$visible_endpoints = array_reduce( $routes, function( $total, $uri ) {
			return $total + count( $uri );
		}, 0 );

		$this->items = $routes;
	}

	/**
	 * Define the columns for our list table
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			'uri'       => __( 'Endpoint URI', 'rest-inspector' ),
			'methods'   => __( 'Methods', 'rest-inspector' ),
			'args'      => __( 'Args', 'rest-inspector' ),
			'callbacks' => __( 'Callback', 'rest-inspector' ),
		];

		return $columns;
	}

	/**
	 * What to print when no items were found
	 */
	function no_items() {
		_e( 'No routes were found.', 'rest-inspector' );
	}

	/**
	 * Display the navigation for the list table
	 *
	 * @param string $which
	 */
	function display_tablenav( $which ) {
		global $plugin_page;

		get_template_part( 'templates/routes-table/tablenav', $which, [
			'page'    => $plugin_page,
			'methods' => explode( ', ', ( REST_Inspector()->get_server() )::ALLMETHODS ),
            'callbacks' => $this::$endpoint_callbacks,
		] );
	}

	/**
	 * Display each row of rewrite rule data
	 */
	function display_rows() {
		foreach ( $this->items as $route => $endpoints ) {
			foreach ( $endpoints as $endpoint ) {
				$endpoint['endpoint_uri'] = $route;
				$this->single_row( $endpoint );
			}
		}
	}

	/**
	 * Display a single row of rewrite rule data
	 */
	function single_row( $item ) {
		$endpoint_uri = $item['endpoint_uri'];

		$private = ! $item['show_in_index'];

		// Collect all methods attached to item.
		$methods = implode( array_map(
			function ( $method ) {
				return "<code>{$method}</code>";
			},
			array_keys( $item['methods'] )
		) );

		$callback = sprintf(
			'<span class="rest-inspector-tooltip-popup"><pre><i>%1$s</i>::%2$s()</pre></span>',
			get_class( $item['callback'][0] ),
			$item['callback'][1]
		);

		$permission_callback = '';
		if ( ! empty( $item['permission_callback'] ) ) {
			$permission_callback = sprintf(
				'<span class="rest-inspector-tooltip-popup"><pre><i>%1$s</i>::%2$s()</pre></span>',
				get_class( $item['permission_callback'][0] ),
				$item['permission_callback'][1]
			);
		}

		$class = 'route-' . ( $private ? 'private' : 'public' );

		echo "<tr class='route-row $class'>";

		list( $columns, $hidden ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {

			switch ( $column_name ) {
				case 'uri':
					echo "<td class='column-endpoint-uri'><strong>" . esc_html( $endpoint_uri ) . "</strong></td>";
					break;
				case 'methods':
					echo "<td class='column-methods'>" . $methods . "</td>";
					break;
				case 'args':
					echo "<td class='column-args'><div class='rest-inspector-json-args' data-rest-json='" . esc_attr( json_encode( $item['args'] ) ) . "'></div></td>";
					break;
				case 'callbacks':
					?>
                    <td class='column-callbacks'>
					    <span class="rest-inspector-tooltip"
                              title="<?php esc_html_e( 'Callback Function', 'rest-inspector' ); ?>">
						    <span class="dashicons dashicons-undo"></span>
						    <?php echo $callback ?>
					    </span>

						<?php if ( ! empty( $permission_callback ) ) : ?>
                            <span class="rest-inspector-tooltip"
                                  title="<?php esc_html_e( 'Permission Callback Function', 'rest-inspector' ); ?>">
							    <span class="dashicons dashicons-lock"></span>
							    <?php echo $permission_callback ?>
						    </span>
						<?php endif; ?>
                    </td>
					<?php

					break;
			}
		}

		echo "</tr>";
	}
}
