<?php
/**
 * Main Admin View
 *
 * @link       https://Alley.co
 * @since      1.0.0
 *
 * @package    Rest_Inspector
 * @subpackage Rest_Inspector/templates
 */

$server = REST_Inspector()->get_server();
$routes = $server->get_routes();

$admin_list_table = new REST_Inspector_List_Table();
$admin_list_table->prepare_items();
?>

<div class="wrap">
    <h2>
        <?php esc_html_e( 'REST Inspector', 'rest-inspector' ); ?>
    </h2>

    <p>
        <?php printf(
			__( 'Found %1$s routes registered on this site.', 'rest-inspector' ),
            count( $routes )
        );
		?>
    </p>

    <?php $admin_list_table->display(); ?>
</div>

