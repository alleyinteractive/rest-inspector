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


if ( !class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . '/wp-admin/includes/class-wp-list-table.php';
}


class REST_Inspector_List_Table extends WP_List_Table {

    /**
     * Construct the list table
     */
    function __construct() {
        parent::__construct(array(
            'singular' => __('Rest Inspector', 'rest-inspector'),
        ));
    }

    /**
     * Load all of the matching rewrite rules into our list table
     */
    function prepare_items() {

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        // Grab routes from REST server instance.
	    $server = REST_Inspector()->get_server();
        $this->items = $server->get_routes();
    }
    
	/**
	 * Define the columns for our list table
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'route'                => __( 'Route', 'rest-inspector' ),
			'methods'              => __( 'Methods', 'rest-inspector' ),
			'args'                 => __( 'Args', 'rest-inspector' ),
			'callbacks'             => __( 'Callback', 'rest-inspector' ),
		);
		return $columns;
	}

    /**
     * What to print when no items were found
     */
    function no_items() {
        _e( 'No REST routes were found.', 'rest-inspector' );
    }

    /**
     * Display the navigation for the list table
     *
     * @param string $which
     */
    function display_tablenav( $which ) {
		// TODO: Use method to insert filter functionality.
    }

    /**
     * Display each row of rewrite rule data
     */
    function display_rows() {
	    foreach ( $this->items as $route => $endpoints ) {
		    foreach ( $endpoints as $endpoint ) {
			    $endpoint['route'] = $route;
			    $this->single_row( $endpoint );
            }
	    }
    }

    /**
     * Display a single row of rewrite rule data
     */
    function single_row( $item ) {

	    $route = $item['route'];

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
			    case 'route':
				    echo "<td class='column-route'><strong>" . esc_html( $route ) . "</strong></td>";
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
					    <span class="rest-inspector-tooltip" title="<?php esc_html_e( 'Callback Function', 'rest-inspector' ); ?>">
						    <span class="dashicons dashicons-undo"></span>
						    <?php echo $callback ?>
					    </span>

						<?php if ( ! empty( $permission_callback ) ) : ?>
						    <span class="rest-inspector-tooltip" title="<?php esc_html_e( 'Permission Callback Function', 'rest-inspector' ); ?>">
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
