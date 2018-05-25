<?php
/**
 * Rest Inspector Meta Box Class.
 *
 * Registers & outputs Rest Inspector meta box.
 *
 * @link       https://Alley.co
 *
 * @package    Rest_Inspector
 * @subpackage Rest_Inspector/inc
 */
class REST_Inspector_Meta_Box {
    use Singleton;

	/**
	 * Setup action hooks responsible for outputting meta box.
	 */
	private static function setup() {
		if ( ! is_admin() ) {
			return;
		}

		/**
		 * Filter Meta Box Priority
		 *
		 * @var int $meta_box_priority
		 */
		$meta_box_priority = apply_filters( 'rest_inspector_meta_box_priority', 1010 );

		// Hook into all registered taxonomies
		add_action( 'registered_taxonomy', function( $taxonomy ) use ( $meta_box_priority ) {
			// Add meta inspector to the bottom of the term edit screen
			add_action( $taxonomy . '_edit_form', array( self::$instance, 'render_term_meta_box' ), $meta_box_priority );
		}, $meta_box_priority );

		// Add meta inspector to posts
		add_action( 'add_meta_boxes', array( self::$instance, 'register_meta_box' ), $meta_box_priority );

		// Add meta box for user pages
		add_action( 'edit_user_profile', array( self::$instance, 'render_user_meta_box'), $meta_box_priority );
		add_action( 'show_user_profile', array( self::$instance, 'render_user_meta_box'), $meta_box_priority );
	}

	/**
	 * Register Meta box for Display
	 */
	public function register_meta_box() {
		switch ( REST_Inspector::$type ) {
			case 'comment' :
				add_meta_box(
					'rest-inspector-metabox',
					__( 'REST Inspector', 'rest-inspector' ),
					array( self::$instance, 'render_comment_meta_box' ),
					REST_Inspector::$type,
					'normal'
				);
				break;

			default:
				add_meta_box(
					'rest-inspector-metabox',
					__( 'REST Inspector', 'rest-inspector' ),
					array( self::$instance, 'render_post_meta_box' ),
					REST_Inspector::$type
				);
				break;
		}
	}

	/**
	 * Render Post Meta Box
	 */
	public function render_post_meta_box() {
		REST_Inspector::$object_id = get_the_ID();
		REST_Inspector::$route_uri = REST_Inspector()->get_the_route();

		$this->render();
	}

	/**
	 * Render Comment Meta Box
	 */
	public function render_comment_meta_box() {
		REST_Inspector::$object_id = get_comment_ID();
		REST_Inspector::$route_uri = REST_Inspector()->get_the_route();

		$this->render();
	}

	/**
	 * Render Term Meta box
	 */
	public function render_term_meta_box() {
		// Ensure the term_id is set
		if ( ! isset( $_GET['tag_ID'] ) ) {
			return;
		}

		REST_Inspector::$type      = 'term';
		REST_Inspector::$object_id = absint( $_GET['tag_ID' ] );
		REST_Inspector::$route_uri = REST_Inspector()->get_the_route();

		$this->render();
	}

	/**
	 * Render User Meta box
	 */
	public function render_user_meta_box() {
		if ( defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE ) {
			REST_Inspector::$object_id = get_current_user_id();
		} elseif ( isset( $_GET['user_id'] ) ) {
			REST_Inspector::$object_id = absint( $_GET['user_id' ] );
		} else {
			return;
		}

		REST_Inspector::$type      = 'user';
		REST_Inspector::$route_uri = REST_Inspector()->get_the_route();

		$this->render();
	}

	/**
	 * Output rest inspector meta box based on current context.
	 */
	public function render() {
	    // grab sample rest response from server/
	    $response = REST_Inspector()->get_rest_response();

	    // var_dump($response->jsonSerialize());exit;

		// Generate a title when necessary.
		switch ( REST_Inspector::$type ) {
			case 'term' :
			case 'user' :
				$title = __( 'REST Inspector', 'rest-inspector' );
				break;
		}

		?>
		<div id="rest-inspector-meta-box"
		     class="rest-inspector__sample-response <?php echo sprintf( 'rest-inspector__%1$s-response', esc_html( REST_Inspector::$type ) ); ?>"
		>
			<?php if ( ! empty( $title ) ) : ?>
				<h3><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>

			<p><?php
				printf(
					__( '<strong>Server Response:</strong> %1$s', 'rest-inspector' ),
					$response->get_status()
				);
				?></p>

			<p><?php
				printf(
					__( '<strong>Route URI:</strong> <code>%1$s</code>', 'rest-inspector' ),
					REST_Inspector::$route_uri
				);
				?></p>

			<?php if ( $response->is_error() ) : ?>
                <p><?php
					printf(
						__( '<strong>Error:</strong> %1$s', 'rest-inspector' ),
						$response->as_error()->get_error_message()
					);
					?></p>
			<?php else : ?>

                <p><?php
					printf(
						__( '<strong>Matched Route URI:</strong> <code>%1$s</code>', 'rest-inspector' ),
						$response->get_matched_route()
					);
					?></p>

				<?php $handler = $response->get_matched_handler(); ?>
                <p><?php
					printf(
						__( '<strong>Callback:</strong> <code><i>%1$s</i>::%2$s()</code>', 'rest-inspector' ),
						get_class( $handler['callback'][0] ),
						$handler['callback'][1]
					);
					?></p>

                <p><?php
					printf(
						__( '<strong>Permissions Callback:</strong> <code><i>%1$s</i>::%2$s()</code>', 'rest-inspector' ),
						get_class( $handler['permission_callback'][0] ),
						$handler['permission_callback'][1]
					);
					?></p>


                <?php

					/**
					 * Hook for adding custom output.
					 *
					 * @var string
					 */
					do_action( 'rest_inspector_meta_box_pre_json', $response );

					// Retrieve the response object.
					$data = $response->jsonSerialize();

					/**
					 * Ensure that `_link` attributes are included in the response. They are handled by the `WP_REST_Controller`
					 * and are not included for internal requests.
					 *
					 * @link https://github.com/WordPress/WordPress/blob/2f792d442bf771a3aade170cc9cae459f024c57b/wp-includes/rest-api/endpoints/class-wp-rest-controller.php#L200-L227
					 */
					if ( empty( $data['_links'] ) ) {
						$links = \WP_REST_Server::get_compact_response_links( $response );

						if ( ! empty( $links ) ) {
							$data['_links'] = $links;
						}
					}
					?>

 					<div class="rest-inspector-json-args"
						data-rest-json="<?php echo esc_attr( json_encode( $data ) ); ?>"
						data-rest-json-depth='1'>
					</div>
			<?php endif; ?>
		</div><!--#rest-inspector-meta-box-->
		<?php
	}
}

function REST_Inspector_Meta_Box() {
	return REST_Inspector_Meta_Box::instance();
}
add_action( 'plugins_loaded', 'REST_Inspector_Meta_Box' );
