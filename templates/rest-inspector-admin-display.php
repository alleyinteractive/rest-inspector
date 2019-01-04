<?php
/**
 * Main Admin View
 *
 * @link       https://Alley.co
 *
 * @package    Rest_Inspector
 * @subpackage Rest_Inspector/templates
 */

$admin_list_table = new \REST_Inspector\WP_List_Table();
$admin_list_table->prepare_items();
?>

<div class="wrap">
    <h2>
        <?php esc_html_e( 'REST Inspector', 'rest-inspector' ); ?>
    </h2>

    <p>
        <?php
        if ( $admin_list_table::$visible_endpoints !== $admin_list_table::$total_endpoints ) {
	        printf(
		        __( 'Displaying %1$d of %2$d registered endpoints.', 'rest-inspector' ),
		        $admin_list_table::$visible_endpoints,
		        $admin_list_table::$total_endpoints
	        );
        } else {
	        printf(
		        __( 'Found %1$d registered REST endpoints.', 'rest-inspector' ),
		        $admin_list_table::$total_endpoints
	        );
        }
		?>
    </p>

    <?php $admin_list_table->display(); ?>
</div>

