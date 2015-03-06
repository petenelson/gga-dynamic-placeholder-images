<?php
if ( ! defined( 'ABSPATH' ) ) wp_die( 'restricted access' );

if ( ! class_exists( 'GGA_Dynamic_Placeholder_Images_API' ) ) {

	class GGA_Dynamic_Placeholder_Images_API {


		private $plugin_name = 'gga-dynamic-images';

		public function plugins_loaded() {
			add_action( 'init', array( $this, 'register_rewrites' ) );
			add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		}


		function register_rewrites() {
			$enabled = apply_filters( $this->plugin_name . '-setting-is-enabled', 'images-api', $this->plugin_name . '-settings-api', 'api-enabled' );
			if ( $enabled ) {

				$endpoint = apply_filters( $this->plugin_name . '-setting-get', 'images-api', $this->plugin_name . '-settings-api', 'api-endpoint' );
				if ( ! empty ( $endpoint ) ) {
					$endpoint .= '/';
				}

				add_rewrite_tag( '%gga-image-api-action%', '([A-Za-z0-9\-\_]+)' );
				add_rewrite_tag( '%gga-image-api-request%', '1' );

				add_rewrite_rule( $endpoint . '([A-Za-z0-9\-\_]+)/?', 'index.php?gga-image-api-request=1&gga-image-api-action=$matches[1]', 'top' );
				add_rewrite_rule( $endpoint . '?', 'index.php?gga-image-api-request=1', 'top' );
			}
		}


		function template_redirect() {

			$enabled = apply_filters( $this->plugin_name . '-setting-is-enabled', 'images-api', $this->plugin_name . '-settings-api', 'api-enabled' );
			if ( $enabled ) {
				global $wp_query;

				if ( $wp_query->get( 'gga-image-api-request' ) === '1' ) {

					$action = $wp_query->get( 'gga-image-api-action' );
					$data = null;

					switch ( $action ) {

						case 'image-tags':
							$data = $this->image_tags_get();
							break;

						default:
							$data = $this->endpoint_list_get();
							break;
					}


					if ( empty ( $data) ) {
						wp_send_json_error();
					} else {
						wp_send_json_success( $data );
					}

				}


			}

		}


		private function endpoint_list_get() {

			$results = array();
			$base_endpoint = apply_filters( $this->plugin_name . '-setting-get', 'images-api', $this->plugin_name . '-settings-api', 'api-endpoint' );

			$endpoints = array(
				'image-tags' => array(
					'description' => __( 'Returns an array of available image tags', 'gga-dynamic-placeholder-images' ),
				),
			);


			foreach ( $endpoints as $endpoint => $details ) {
				$o = new stdClass();
				$o->endpoint = $endpoint;
				$o->url = trim( site_url( trailingslashit( path_join( $base_endpoint, $endpoint ) ) ) );
				$o->details = $details;
				$results[] = $o;
			}

			return $results;

		}


		private function image_tags_get() {

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
