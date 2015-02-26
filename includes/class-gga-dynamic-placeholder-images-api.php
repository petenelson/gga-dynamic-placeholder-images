<?php

if ( !defined( 'ABSPATH' ) ) exit( 'restricted access' );

if ( ! class_exists( 'GGA_Dynamic_Placeholder_Images_API' ) ) {

	class GGA_Dynamic_Placeholder_Images_API {


		public function plugins_loaded() {
			add_action( 'init', array( $this, 'register_rewrites' ) );
			add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		}


		function register_rewrites() {
			add_rewrite_tag( '%gga-image-api-action%', '([A-Za-z0-9\-\_]+)' );
			add_rewrite_rule( 'images-api/([A-Za-z0-9\-\_]+)/?', 'index.php?gga-image-api-action=$matches[1]', 'top' );
		}


		function template_redirect() {

			global $wp_query;

			$action = $wp_query->get( 'gga-image-api-action' );

			switch ( $action ) {

				case 'image-tags':
					$tags = $this->image_tags_get();
					if ( empty ( $tags ) )
						wp_send_json_error();
					else
						wp_send_json_success( $tags );

			}

		}


		function image_tags_get() {

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
				'nopaging' => true,
				'no_found_rows' => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			);


			global $post;
			$query = new WP_Query( $args );
			while ( $query->have_posts() ) {
				$query->the_post();
				$tags[] = $post->post_name;
			}

			wp_reset_postdata();

			return $tags;

		}

	}


}
