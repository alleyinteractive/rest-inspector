<?php
/**
 * Template partial displayed at top of Rest Inspector admin list table.
 *
 * @link       https://Alley.co
 *
 * @package    Rest_Inspector
 * @subpackage Rest_Inspector/templates/routes-table
 */

$plugin_page         = $template_var_page;
$available_methods   = $template_var_methods ?? [];
$available_callbacks = $template_var_callbacks ?? [];

$default_option = 'all';

// Filter by query.
$search = isset( $_GET['s'] ) ? esc_html( sanitize_text_field( $_GET['s'] ) ) : '';

// Filter by endpoint HTTP method.
$method = isset( $_GET['method'] ) ? strtoupper( esc_html( sanitize_key( $_GET['method'] ) ) ) : '';
array_unshift( $available_methods, $default_option );
$selected_method = in_array( $method, $available_methods ) ? $method : $default_option;

// Filter by callbacks.
$callback = isset( $_GET['callback'] ) ? str_replace('\\\\', '\\', esc_html( sanitize_text_field( $_GET['callback'] ) ) ) : '';
array_unshift( $available_callbacks, $default_option );
$selected_callback = in_array( $callback, $available_callbacks ) ? $callback : $default_option;

?>

<div class="rest-inspector-tablenav-top">
    <form method="GET">
        <label for="s"><?php esc_html_e( 'Search Endpoint:', 'rest-inspector' ); ?></label>
        <input type="text" id="s" name="s" value="<?php echo esc_attr( $search ); ?>" size="60"/>

        <label for="method"><?php esc_html_e( 'Method:', 'rest-inspector' ); ?></label>
        <select id="method" name="method">
			<?php
			foreach ( $available_methods as $option ) {
				echo '<option value="' . esc_attr( $option ) . '" ';
				selected( $selected_method, $option );
				echo '>' . esc_attr( $option ) . '</option>';
			}
			?>
        </select>

        <label for="callback"><?php esc_html_e( 'Callback:', 'rest-inspector' ); ?></label>
        <select id="callback" name="callback">
			<?php
			foreach ( $available_callbacks as $option ) {
				echo '<option value="' . esc_attr( $option ) . '" ';
				selected( $selected_callback, $option );
				echo '>' . esc_attr( $option ) . '</option>';
			}
			?>
        </select>

		<?php submit_button( __( 'Filter', 'rest-inspector' ), 'primary', null, false ); ?>

		<?php if ( $search || ! empty( $method ) ): ?>
            <a href="<?php menu_page_url( $plugin_page ); ?>"
               class="button-secondary"><?php esc_html_e( 'Reset', 'rest-inspector' ); ?></a>
		<?php endif; ?>

        <input type="hidden" id="page" name="page" value="<?php echo esc_attr( $plugin_page ); ?>"/>
    </form>
</div>