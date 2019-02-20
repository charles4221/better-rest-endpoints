<?php
/**
 * Return ACF Fields
 *
 * @param none
 * @return object|null ACF object,  * or null if none.
 * @since 0.0.1
 */

function deep_modify_acf_post_objects( &$item, $key ) {
	// Post Object field is the only one with the 'post_type' property.
	if ( isset($item->post_type) ) {
		$item = array(
			'id' => $item->ID,
			'slug' => $item->post_name,
			'type' => $item->post_type,
			'title' => $item->post_title,
			'permalink' => get_the_permalink( $item->ID )
		);
	}
}

function bre_get_acf() {
  
  include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

  // check if acf is active before doing anything
   if( is_plugin_active('advanced-custom-fields-pro/acf.php') || is_plugin_active('advanced-custom-fields/acf.php') ) {

     // get fields
     $acf_fields = get_fields();

     // if we have fields
     if( $acf_fields ) {
	 	// Modify all post object responses.
	 	array_walk_recursive( $acf_fields, 'deep_modify_acf_post_objects' );
     	return $acf_fields;
     }

   } else {
     // no acf, return false
     return false;
   }
}
