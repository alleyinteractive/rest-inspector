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
		if ( \REST_Inspector::$total_endpoints !== \REST_Inspector::$visible_endpoints ) { // phpcs:disable WordPress.PHP.YodaConditions.NotYoda
			printf(
				/* translators: %1$s: Visible endpoints, %2$s: Total endpoints. */
				esc_html__( 'Displaying %1$d of %2$d registered endpoints.', 'rest-inspector' ),
				esc_html( \REST_Inspector::$visible_endpoints ),
				esc_html( \REST_Inspector::$total_endpoints )
			);
		} else {
			printf(
				/* translators: %d: Total number of endpoints. */
				esc_html__( 'Found %1$d registered REST endpoints.', 'rest-inspector' ),
				esc_html( \REST_Inspector::$total_endpoints )
			);
		}
		?>
	</p>

	<?php $admin_list_table->display(); ?>
</div>

