<?php

/**
 * Retrieve single ACF options page value
 *
 * @param WP_REST_Request $request
 * @return array|void
 * @since 1.3.0
 */
function get_options_acf_single( WP_REST_Request $request ){
    include_once ( ABSPATH . 'wp-admin/includes/plugin.php' );

    // Only run if we have ACF installed
    if( !is_plugin_active('advanced-custom-fields-pro/acf.php') && !is_plugin_active('advanced-custom-fields/acf.php') ) {
        return;
    }

    if($field = get_field($request['field'], 'option')) {
        return $field;
    } else {
        return;
    }
}

/**
 * Retrieve array of all ACF options page values
 *
 * @return array
 * @since 1.3.0
 */
function get_options_acf_all(){
    include_once ( ABSPATH . 'wp-admin/includes/plugin.php' );

    // Only run if we have ACF installed
    if( !is_plugin_active('advanced-custom-fields-pro/acf.php') && !is_plugin_active('advanced-custom-fields/acf.php') ) {
        return array();
	}
	
	// get fields
	$acf_options = get_fields('options');

	// if we have fields
	if ( $acf_options ) {
		// Modify all post object responses.
		array_walk_recursive( $acf_options, 'deep_modify_acf_post_objects' );
		return $acf_options;
	} else {
		return array();
	}
}

/**
 * Register REST APIs
 */
add_action( 'rest_api_init', function () {
    register_rest_route( 'better-rest-endpoints/v1', '/options/acf', array(
        'methods' => 'GET',
        'callback' => 'get_options_acf_all',
    ));
    register_rest_route( 'better-rest-endpoints/v1', '/options/acf/(?P<field>\S+)', array(
        'methods' => 'GET',
        'callback' => 'get_options_acf_single',
    ));
});
