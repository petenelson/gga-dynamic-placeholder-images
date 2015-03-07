<?php
if ( ! defined( 'ABSPATH' ) ) wp_die( 'restricted access' );

if ( ! class_exists( 'GGA_Dynamic_Placeholder_Images_Core' ) ) {

	class GGA_Dynamic_Placeholder_Images_Core {

		private $version = '2015-03-06-01';
		private $sizes;
		private $plugin_name = 'gga-dynamic-images';

		var $plugin_base_url = '';


		static public function get_request( $key, $default = '', $filter = FILTER_SANITIZE_STRING ) {
			foreach (array( INPUT_GET, INPUT_POST ) as $input) {
				$value = filter_input( $input, $key, $filter );
				if ( ! empty( $value ) ) {
					return $value;
				}
			}
			return $default;
		}


		public function plugins_loaded() {

			load_plugin_textdomain( 'gga-dynamic-placeholder-images' );

			add_action( 'init', array( $this, 'register_rewrites' ) );
			add_action( 'template_redirect', array( $this, 'template_redirect' ) );
			add_action( 'delete_attachment', array( $this, 'delete_attachment' ) );

			// generate a URL to an image
			add_filter( $this->plugin_name . '-image-url', array( $this, 'generate_image_url' ), 10, 4 );


			add_action( $this->plugin_name . '-delete-associations', array( $this, 'delete_all_dimension_associations' ) );
			add_filter( $this->plugin_name . '-get-associations-count', array( $this, 'get_dimension_associations_count' ) );


		}


		public function register_rewrites() {
			add_rewrite_tag( '%gga-dynamic-image%', '1' );
			add_rewrite_tag( '%gga-dynamic-image-width%', '([0-9]+)' );
			add_rewrite_tag( '%gga-dynamic-image-height%', '([0-9]+)' );
			add_rewrite_tag( '%gga-dynamic-image-slug%', '([A-Za-z0-9\-\_]+)' );

			add_rewrite_rule( $this->get_base_url() . '([0-9]+)/([0-9]+)/([A-Za-z0-9\-\_]+)/?', 'index.php?gga-dynamic-image=1&gga-dynamic-image-width=$matches[1]&gga-dynamic-image-height=$matches[2]&gga-dynamic-image-slug=$matches[3]', 'top' );
			add_rewrite_rule( $this->get_base_url() . '([0-9]+)/([0-9]+)/?', 'index.php?gga-dynamic-image=1&gga-dynamic-image-width=$matches[1]&gga-dynamic-image-height=$matches[2]&gga-dynamic-image-slug=', 'top' );
		}


		public function template_redirect() {

			global $wp_query;

			$action = $wp_query->get( 'gga-dynamic-image' );

			if ( ! empty( $action ) ) {
				$width = intval( $wp_query->get( 'gga-dynamic-image-width' ) );
				$height = intval( $wp_query->get( 'gga-dynamic-image-height' ) );
				$slug = sanitize_key( $wp_query->get( 'gga-dynamic-image-slug' ) );

				// bounds checking, defaults to a max of 2000x2000
				$max_width = $this->get_max_width();
				$max_height = $this->get_max_height();
				$width = $width > $max_width ? $max_width : $width;
				$height = $height > $max_height ? $max_height : $height;

				$this->handle_dynamic_image( $width, $height, $slug );
			}

		}


		public function generate_image_url( $url, $width, $height, $tag = '' ) {
			$url = site_url( $this->get_base_url() . intval( $width ) . '/' . intval( $height ) . '/' . sanitize_key( $tag ) . '/' );
			return $url;
		}


		private function get_base_url( $base_url = '' ) {
			$base_url = apply_filters( $this->plugin_name . '-setting-get', 'dynamic-image', 'gga-dynamic-images-settings-general', 'base-url' );
			return ! empty( $base_url ) ? $base_url . '/' : '';
		}


		private function handle_dynamic_image( $width, $height, $slug ) {

			global $wp_query;


			if ( $width !== 0 && $height !== 0 ) {

				//$this->load_image_sizes();
				$id = 0;

				if ( $slug === 'random' ) {
					$id = $this->get_random_image_id();
					$this->add_expires = false;
				}
				else if ( !empty( $slug ) ) {
						$id = $this->get_image_id_by_slug( $slug );
						if ( $id === 0 ) {
							$this->show_404_and_die();
						}
					} else {
					$id = $this->get_existing_image_id_by_dimensions( $width, $height );
				}


				if ( empty( $id ) ) {
					$id = $this->get_random_image_id();
				}

				if ( empty( $id ) ) {
					$this->show_404_and_die();
				}
				else {
					$this->generate_image( $id, $width, $height );
					$this->stream_image( $id, $width, $height );
				}

				die();
			}


		}


		public function delete_attachment( $postid ) {
			$this->delete_options_from_query( " WHERE option_name like '_gga-placeholder-image-for%' and option_value = '" . intval( $postid ) . "'" );
		}


		public function delete_all_dimension_associations() {
			if ( current_user_can( 'manage_options' ) ) {
				$this->delete_options_from_query( " WHERE option_name like '_gga-placeholder-image-for%'" );
			}
		}


		public function get_dimension_associations_count( $count ) {
			global $wpdb;
			$count = $wpdb->get_var( "SELECT count(*) FROM $wpdb->options WHERE option_name like '_gga-placeholder-image-for%'" );
			return $count;
		}


		function delete_options_from_query( $query ) {
			global $wpdb;
			$results = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options " . $query );

			foreach ( $results as $r ) {
				delete_option( $r->option_name );
			}

		}


		function show_404_and_die() {
			status_header( 404 );
			nocache_headers();
			include get_404_template();
			//die();
		}


		public function query_images( $args ) {
			global $post;
			$posts = array();
			$query = new WP_Query( $args );

			while ( $query->have_posts() ) {
				$query->the_post();
				$posts[] = $post;
			}

			wp_reset_postdata();
			return $posts;
		}


		public function image_query_args() {

			return array(
				'posts_per_page' => 1,
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'post_mime_type' => 'image',
				'meta_key' => '_gga_image_is_mockup_image',
				'meta_value' => 'on',
			);

		}


		function get_random_image_id() {
			$args = $this->image_query_args();
			$args['orderby'] = 'rand';
			return $this->get_image_id_from_query( $args );
		}


		function get_image_id_by_slug( $slug ) {
			$args = $this->image_query_args();
			$args['name'] = $slug;
			return $this->get_image_id_from_query( $args );
		}


		function get_image_id_from_query( $args ) {
			$id = 0;
			$query = new WP_Query( $args );

			if ( $query->have_posts() )
				$id = $query->post->ID;

			return $id;
		}


		function stream_image( $id, $w, $h ) {

			$image_size_name = $this->image_size_name( $w, $h );
			$image = get_post( $id );

			$meta = wp_get_attachment_metadata( $id );
			if ( ! empty( $meta[ $this->meta_sizes ] ) ) {
				$sizes = $meta[ $this->meta_sizes ];
			}

			if ( !empty( $sizes ) && ! empty( $sizes[ $image_size_name ] ) ) {

				$filename = $this->get_cached_file_path( $sizes[ $image_size_name ]['file'], $w );
				if ( ! file_exists( $filename ) ) {
					// regenrate a missing image
					$this->generate_image( $id, $w, $h );
				}

				$filesize = filesize( $filename );

				header( 'Content-Type: ' . $sizes[$image_size_name]['mime-type'] );
				header( 'Content-Length: ' . $filesize );
				header( 'Content-Disposition: inline; filename=' . $image->post_name . '-' . $w . '-' . $h . '.jpg' );

				if ( $this->add_expires ) {
					$expires = DAY_IN_SECONDS * 15;
					header( 'Pragma: public' );
					header( 'Cache-Control: public' );
					header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time()+$expires ) . ' GMT' );
					header( 'Last-Modified:Mon, 20 Aug 2012 19:20:21 GMT' );
				}


				ob_clean();
				flush();
				readfile( $filename );

				// fire action to allow stats logging
				do_action( $this->plugin_name . '-image-view', array( 'post_id' => $id, 'width' => $w, 'height' => $h, 'bytes' => $filesize ) );

				die();

			}
			else {
				$this->show_404_and_die();
			}

		}


		function save_images_sizes() {
			$key = 'gga-dynamic-image-sizes';
			if ( false === get_option( $key ) )
				add_option( $key, $this->sizes, $deprecated = '', $autoload = 'no' );
			else
				update_option( $key, $this->sizes );
		}


		function load_image_sizes() {
			$this->sizes = get_option( 'gga-dynamic-image-sizes' );

			if ( $this->sizes && is_array( $this->sizes ) ) {
				foreach ( $this->sizes as $s )
					add_image_size( $s->name, $s->w, $s->h, true );
			}
			else
				$this->sizes = array();

		}


		function add_image_size( $w, $h ) {
			$image_size_name = $this->image_size_name( $w, $h );

			foreach ( $this->sizes as $s ) {
				if ( $s->name === $image_size_name )
					return;
			}


			// size does not exist, add it
			$size = new stdClass();
			$size->name = $image_size_name;
			$size->w = $w;
			$size->h = $h;

			$this->sizes[] = $size;
			//update_option( 'gga-dynamic-image-sizes',  $this->sizes);
			//$this->save_images_sizes();

			add_image_size( $image_size_name, $w, $h, true );

		}



		function generate_image( $id, $w, $h ) {


			$image_size_name = $this->image_size_name( $w, $h );

			$this->add_image_size( $w, $h );

			$image = get_post( $id );
			$image_size_exists = $this->image_size_exists( $id, $w, $h );
			$cached_file_exists = false;

			$metadata = wp_get_attachment_metadata( $id );

			if ( ! empty( $metadata ) && ! empty( $metadata[ $this->meta_sizes ] ) && ! empty( $metadata[ $this->meta_sizes ][ $image_size_name ] ) && ! empty( $metadata[ $this->meta_sizes ][ $image_size_name ]['file'] ) ) {
				$cached_file_exists = file_exists( $this->get_cached_file_path( $metadata[ $this->meta_sizes ][ $image_size_name ]['file'], $w ) );
			}


			if ( ! $image_size_exists || ! $cached_file_exists ) {
				if ( $image ) {
					$fullsizepath = get_attached_file( $image->ID );
					include_once ABSPATH . 'wp-admin/includes/image.php';

					// don't call wp_generate_attachment_metadata because it regenerates existing images

					if ( false ) {
						// this will force the resizer to conform to the dimensions of the original image
						// we may need to add code here to scale up the original image before resizing/cropping
						if ( ! empty( $metadata['width'] ) && $w > $metadata['width'] ) {
							$w = $metadata['width'];
						}

						if ( ! empty( $metadata['height'] ) && $w > $metadata['height'] ) {
							$h = $metadata['height'];
						}
					}

					if ( ! $cached_file_exists ) {
						$resized = image_make_intermediate_size( $fullsizepath, $w, $h, $crop=true );
						if ( ! empty( $resized ) ) {
							$this->move_resized_to_cache( $resized['file'], $fullsizepath, $w );
						} else {
							return false;
						}
					}


					if ( ! empty( $resized ) && ! empty( $resized['file'] ) ) {
						$metadata[ $this->meta_sizes ][ $image_size_name ] = $resized;
						wp_update_attachment_metadata( $id, $metadata );
					}

				}

			}


			// save option so we know which image to use for the size
			if ( empty( $this->get_existing_image_id_by_dimensions( $w, $h ) ) )
				add_option( "_gga-placeholder-image-for-{$w}-{$h}", $id, '', 'no' );

			return true;

		}


		function move_resized_to_cache( $resized_filename, $fullsizepath, $width ) {
			delete_site_transient( $this->plugin_name . '-cache-size' );
			rename( path_join( dirname($fullsizepath), $resized_filename ), path_join( $this->get_cache_directory_for_width( $width ), $resized_filename ) );
		}


		function get_existing_image_id_by_dimensions( $w, $h ) {
			return get_option( "_gga-placeholder-image-for-{$w}-{$h}", false );
		}


		function image_size_exists( $id, $w, $h ) {
			$meta = wp_get_attachment_metadata( $id );
			return ! empty( $meta[ $this->meta_sizes ] ) && ! empty( $meta[ $this->meta_sizes ][$this->image_size_name( $w, $h )] );
		}


		function image_size_name( $w, $h ) {
			return 'gga-image-' . $w . '-' . $h;
		}


		function init_filesystem() {
			// TODO remove
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			$access_type = get_filesystem_method();
			if( $access_type === 'direct' ) {
				$upload_dir = wp_upload_dir();
				$creds = request_filesystem_credentials( $upload_dir['url'] );
				/* initialize the API */
				return WP_Filesystem( $creds );
			} else {
				return false;
			}
		}


		private function get_cache_directory() {
			return apply_filters( $this->plugin_name . '-get-cache-directory', 'gga-dynamic-placeholder-images' );
		}




		function get_cache_directory_contents() {
			return apply_filters( $this->plugin_name . '-get-cache-directory-contents', false );
		}


		function get_cache_directory_size( $size ) {
			return apply_filters( $this->plugin_name . '-get-cache-size', 0 );
		}


		function get_cache_directory_for_width( $width ) {
			$max_width = $this->get_max_width();
			if ( $width > $max_width ) {
				$width = $max_width;
			}

			$width_directory = strval( floor( $width / 100 ) * 100 );
			if ( $width_directory === '0' )
				$width_directory = '1';

			$width_directory = path_join( $this->get_cache_directory(),  $width_directory );
			if ( ! is_dir( $width_directory ) ) {
				wp_mkdir_p( $width_directory );
			}

			return $width_directory;
		}


		function get_cached_file_path( $filename, $width ) {
			return path_join( $this->get_cache_directory_for_width( $width ), $filename );
		}


		function get_max_width() {
			return intval( apply_filters( $this->plugin_name . '-setting-get', 2000, $this->plugin_name . '-settings-general', 'max-width' ) );
		}


		function get_max_height() {
			return intval( apply_filters( $this->plugin_name . '-setting-get', 2000, $this->plugin_name . '-settings-general', 'max-height' ) );
		}


		// because Sneek read the code

	}


}
