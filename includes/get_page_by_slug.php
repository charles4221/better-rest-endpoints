<?php
/**
 * Grab a page by slug
 *
 * @param array $data Options for the function.
 * @return array|null Single page data,  * or null if none.
 * @since 0.0.1
 */

function get_page_by_slug( WP_REST_Request $request ) {

	$page_slug = $request['slug'];

	// WP_Query arguments
	if ( strpos( $page_slug, '/' ) === false ) {
		$args = array(
			'post_type' => 'page',
			'name' => $page_slug,
			'post_parent' => 0,
		);
	} else {
		$page = get_page_by_path( $page_slug );
		$args = array(
			'page_id' => $page->ID,
		);
	}

	// The Query
	$query = new WP_Query( $args );

	// The Loop
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();

			global $post;

			// better WordPress endpoint post object
			$bre_page = new stdClass();

			$permalink = get_permalink();
			$bre_page->id = get_the_ID();
			$bre_page->title = get_the_title();
			$bre_page->slug = $post->post_name;
			$bre_page->permalink = $permalink;
			$bre_page->date = get_the_date( 'c' );
			$bre_page->date_modified = get_the_modified_date( 'c' );

			/*
			*
			* return template name
			*
			*/
			if ( get_page_template() ) {
				// strip file extension to return just the name of the template
				$template_name = preg_replace( '/\\.[^.\\s]{3,4}$/', '', basename( get_page_template() ) );

				$bre_page->template = $template_name;

			} else {
				$bre_page->template = 'default';
			}

			$bre_page->content = apply_filters( 'the_content', get_the_content() );

			/*
			*
			* return parents if they exist
			*
			*/
			$anc = array_map( 'get_post', array_reverse( (array) get_post_ancestors( $post ) ) );
			$parents = array();
			foreach ($anc as $parent) {
				$obj = new stdClass();
				$obj->id = $parent->ID;
				$obj->title = $parent->post_title;
				$obj->slug = $parent->post_name;
				$obj->permalink = get_permalink( $parent );
				$obj->type = $parent->post_type;
				array_push( $parents, $obj );
			}
			$bre_page->parents = $parents ? $parents : false;

			/*
			*
			* return acf fields if they exist
			*
			*/
			$bre_page->acf = bre_get_acf();

			/*
			*
			* return Yoast SEO fields if they exist
			*
			*/
			$bre_page->yoast = bre_get_yoast( $bre_page->id );

			/*
			*
			* get possible thumbnail sizes and urls
			*
			*/
			$thumbnail_names = get_intermediate_image_sizes();
			$bre_thumbnails = new stdClass();

			if ( has_post_thumbnail() ) {
				foreach ( $thumbnail_names as $key => $name ) {
					$bre_thumbnails->$name = esc_url( get_the_post_thumbnail_url( $post->ID, $name ) );
				}

				$bre_page->media = $bre_thumbnails;
			} else {
				$bre_page->media = false;
			}

			// Push the post to the main $post array
			return $bre_page;

		}
	} else {
		// no posts found
		$bre_page = [];

		return $bre_page;
	}

	// Restore original Post Data
	wp_reset_postdata();
}

  add_action(
	  'rest_api_init', function () {
		register_rest_route(
			'better-rest-endpoints/v1', '/page/(?P<slug>\S+)', array(
				'methods' => 'GET',
				'callback' => 'get_page_by_slug',
			)
		);
	  }
  );
