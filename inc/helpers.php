<?php
/**
 * Helper Functions
 *
 * @author griffenfargo
 * @package rest-inspector
 */

namespace REST_Inspector;

/**
 * Wrapper for core's default template loader that accepts array of arguments.
 *
 * Arguments passed through are prefixed with `template_var`.  This means if
 * the 'post_id' key exists in $args it is available inside the template
 * under $template_var_post_id.
 *
 * @param string      $slug The slug name for the generic template.
 * @param string|null $name The name of the specialised template.
 * @param array       $args Collection of parameters accessible to the template.
 */
function get_template_part( $slug, $name = null, $args = [] ) {
	$path = REST_INSPECTOR_ROOT . '/' . $slug;

	if ( ! empty( $name ) ) {
		$path= "{$path}-{$name}";
	}

	extract( $args, EXTR_PREFIX_ALL,  'template_var' );

	include "{$path}.php";
}