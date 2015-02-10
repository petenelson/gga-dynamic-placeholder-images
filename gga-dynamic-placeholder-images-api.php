<?php
/*
Plugin Name: GGA Dynamic Placeholder Images API
Description: Adds an 'images-api' endpoint to expose image data via JSON
Author: Pete Nelson
Version: 1.0
*/

if (!defined( 'ABSPATH' )) exit('restricted access');


add_action( 'init', 'gga_dynamic_images_api_endpoints' );

if ( ! function_exists( 'gga_dynamic_images_api_endpoints' ) ) {
	function gga_dynamic_images_api_endpoints() {
		add_rewrite_tag( '%gga-image-api-action%', '([A-Za-z0-9\-\_]+)' );
		add_rewrite_rule( 'images-api/([A-Za-z0-9\-\_]+)/?', 'index.php?gga-image-api-action=$matches[1]', 'top' );
	}
}


add_action( 'template_redirect', 'gga_dynamic_images_prefix_do_api' );

if ( ! function_exists( 'gga_dynamic_images_prefix_do_api' ) ) {
	function gga_dynamic_images_prefix_do_api() {
		global $wp_query;

		$action = $wp_query->get( 'gga-image-api-action' );

		switch ($action) {

			case 'image-tags':
				$tags = gga_dynamic_image_tags_get();
				if ( empty ( $tags ) )
					wp_send_json_error();
				else
					wp_send_json_success( $tags );

		}


	}
}


if ( ! function_exists( 'gga_dynamic_image_tags_get' ) ) {

	function gga_dynamic_image_tags_get() {

		$tags = array();

		$args = array(
			'posts_per_page' => -1,
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'post_mime_type' => 'image',
			'meta_key' => '_gga_image_is_mockup_image',
			'meta_value' => 'on',
			'orderby' => 'name',
			'order' => 'asc',
		);


		global $post;
		$query = new WP_Query($args);
		while ($query->have_posts()) {
			$query->the_post();
			$tags[] = $post->post_name;
		}

		wp_reset_postdata();

		return $tags;

	}

}

