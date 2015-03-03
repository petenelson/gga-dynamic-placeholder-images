<?php

if ( !defined( 'ABSPATH' ) ) exit( 'restricted access' );

if ( ! class_exists( 'GGA_Dynamic_Placeholder_Images_Core' ) ) {

	class GGA_Dynamic_Placeholder_Images_Core {

		var $version = '2015-02-26-01';
		var $sizes;
		var $meta_prefix = '_gga_image_';
		var $meta_sizes = '_gga_dpi_sizes';
		var $options = '_gga_placeholder_image_options';
		var $plugin_name = 'gga-dynamic-images';
		var $add_expires = true;
		var $plugin_base_url = '';


		public function plugins_loaded() {

			load_plugin_textdomain( 'gga-dynamic-placeholder-images' );

			add_action( 'init', array( $this, 'register_rewrites' ) );
			add_action( 'template_redirect', array( $this, 'template_redirect' ) );
			add_action( 'delete_attachment', array( $this, 'delete_attachment' ) );

			// displays the image attribution list
			add_shortcode( 'gga-image-attribution', array( $this, 'image_attribution_shortcode' ) );

			// for generating Creative Commons icons
			add_filter( $this->plugin_name . '-cc-img-html', array( $this, 'cc_img_html' ), 10, 2 );

			// allows cache interaction
			add_action( $this->plugin_name . '-purge-cache', array( $this, 'purge_cache_directory' ) );
			add_filter( $this->plugin_name . '-get-cache-size', array( $this, 'get_cache_directory_size' ) );

		}


		function register_rewrites() {
			add_rewrite_tag( '%gga-dynamic-image%', '1' );
			add_rewrite_tag( '%gga-dynamic-image-width%', '([0-9]+)' );
			add_rewrite_tag( '%gga-dynamic-image-height%', '([0-9]+)' );
			add_rewrite_tag( '%gga-dynamic-image-slug%', '([A-Za-z0-9\-\_]+)' );

			add_rewrite_rule( $this->get_base_url() . '([0-9]+)/([0-9]+)/([A-Za-z0-9\-\_]+)/?', 'index.php?gga-dynamic-image=1&gga-dynamic-image-width=$matches[1]&gga-dynamic-image-height=$matches[2]&gga-dynamic-image-slug=$matches[3]', 'top' );
			add_rewrite_rule( $this->get_base_url() . '([0-9]+)/([0-9]+)/?', 'index.php?gga-dynamic-image=1&gga-dynamic-image-width=$matches[1]&gga-dynamic-image-height=$matches[2]&gga-dynamic-image-slug=', 'top' );
		}


		function template_redirect() {

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


		function get_base_url() {
			$base_url = apply_filters( $this->plugin_name . '-setting-get', 'dynamic-image', 'gga-dynamic-images-settings-general', 'base-url' );
			return ! empty( $base_url ) ? $base_url . '/' : '';
		}


		function handle_dynamic_image( $width, $height, $slug ) {

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


		function admin_menu() {
			add_options_page( __( 'GGA Dynamic Image Options', 'gga-dynamic-placeholder-images' ), __( 'GGA Dynamic Image', 'gga-dynamic-placeholder-images' ), 'manage_options', 'gga-dynamic-image-options', array( $this, 'admin_options_page' ) );
		}


		function admin_options_page() {

			$status_message = '';

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to manage options for this site.' ) );
			}

			if ( isset( $_POST['_wpnonce'] ) ) {
				if ( !wp_verify_nonce( $_POST['_wpnonce'], 'gga-clear-dimension-associations' ) )
					wp_die( 'Invalid nonce' ) ;


				if ( isset( $_POST['gga-clear-dimension-associations'] ) ) {

					$this->delete_all_dimension_associations();
					$status_message = __( 'Dimensions cleared' , 'gga-dynamic-placeholder-images' );

				}


			}

			$opt = $this->options;
			?>

			<div class="wrap">
				<div id="icon-options-general" class="icon32"></div>
				<h2><?php _e( 'GGA Dynamic Image Options' , 'gga-dynamic-placeholder-images' ) ?></h2>
				<?php
					if ( !empty( $status_message ) ) {
						?><div class="updated"><p><strong><?php echo $status_message ?></strong></p></div><?php
					}
				?>
				<form method="post" action="options.php" id="gga-dynamic-image-settings">
					<?php
						settings_fields( $opt );
						do_settings_sections( $opt );
						submit_button();
					?>
				</form>

				<form method="post" action="options-general.php?page=gga-dynamic-image-options" id="gga-dynamic-image-settings">
					<?php wp_nonce_field( $action = 'gga-clear-dimension-associations', $name = '_wpnonce', $referer = true, $echo = true ) ?>

					<?php _e( 'When an image is first generated, the requested dimensions are associated with the image. Subsequent requests for those dimensions will return the same image. Clearing the associations will allow the plugin to create new ones.', 'gga-dynamic-placeholder-images' ) ?>

					<?php
						submit_button( __( 'Clear Dimension Associations', 'gga-dynamic-placeholder-images' ), $type = 'secondary', $name = 'gga-clear-dimension-associations', $wrap = true, $other_attributes = null );
					?>
				</form>


			</div>

			<?php

			flush_rewrite_rules( );
		}


		function delete_attachment( $postid ) {
			$this->delete_options_from_query( " WHERE option_name like '_gga-placeholder-image-for%' and option_value = '" . intval( $postid ) . "'" );
		}


		function delete_all_dimension_associations() {
			$this->delete_options_from_query( " WHERE option_name like '_gga-placeholder-image-for%'" ) ;
		}


		function delete_options_from_query( $query ) {
			global $wpdb;
			$results = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options " . $query );

			foreach ( $results as $r )
				delete_option( $r->option_name );

		}


		function show_404_and_die() {
			status_header( 404 );
			nocache_headers();
			include get_404_template();
			die();
		}


		function query_images( $args ) {
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


		function image_query_args() {

			return array(
				'posts_per_page' => 1,
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'post_mime_type' => 'image',
				'meta_key' => $this->meta_prefix . 'is_mockup_image',
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


		function image_attribution_shortcode( $args ) {

			$args = wp_parse_args( $args, array(
					'width' => 300,
					'height' => 300,
					'columns' => 3,
					'class' => 'gga-dynamic-images-attribution',
				)
			);

			$int_args = array( 'width', 'height', 'columns' );
			foreach ( $int_args as $key ) {
				$args[ $key ] = intval( $args[ $key ] );
			}

			if ( $args['columns'] < 1 )
				$args['columns'] = 1;

			if ( $args['width'] < 1 )
				$args['width'] = 1;

			if ( $args['height'] < 1 )
				$args['height'] = 1;

			$args['class'] = esc_attr( $args['class'] );

			wp_enqueue_style( $this->plugin_name . '-attribution', $this->plugin_base_url . 'public/css/gga-dynamic-images.css', array(), $this->version );

			$html = '<div class="' . $args['class'] . '">';

			$query_args = $this->image_query_args();
			$query_args['posts_per_page'] = -1;
			$query_args['orderby'] = 'name';
			$query_args['order'] = 'asc';

			$posts = $this->query_images( $query_args );

			foreach( $posts as $post ) {

				$id = $post->ID;

				$tag = $post->post_name;
				$image_url = site_url( $this->get_base_url() . $args['width'] . '/' . $args['height'] . '/' . $tag );
				$cc_url = '';
				$attrib_to = get_post_meta( $id, $this->meta_prefix . 'attribute_to', true );
				$attrib_url = get_post_meta( $id, $this->meta_prefix . 'attribute_url', true );

				if ( 'on' === get_post_meta( $id, $this->meta_prefix . 'cc_by', true ) );
					$cc_url = 'http://creativecommons.org/licenses/by/2.0/';

				if ( 'on' === get_post_meta( $id, $this->meta_prefix . 'cc_sa', true ) );
					$cc_url = 'http://creativecommons.org/licenses/by-sa/2.0/';

				$html .= "
				<div class=\"attribImage\">
					<a class=\"image-link\" href=\"{$image_url}\"><img class=\"image-thumbnail\" src=\"{$image_url}\" alt=\"\" width=\"" . $args['width'] . "\" height=\"" . $args['height'] . "\" /></a>
					<div class=\"image-tag\">tag: {$tag}</div>";

				if ( $attrib_to !== false && !empty( $attrib_to ) )
					$html .= "<div class=\"attribute-to\">by <a href=\"{$attrib_url}\" target=\"_blank\">" . htmlspecialchars( $attrib_to ) . "</a></div>";

				if ( 'on' === get_post_meta( $id, $this->meta_prefix . 'cc_by', true ) )
					$html .= '<span class="cc cc-by" title="' . __( 'Creative Commons Attribution', 'gga-dynamic-placeholder-images' ) . '">' . $this->cc_img_html( '', 'cc_by' ) . '</span>';

				if ( 'on' === get_post_meta( $id, $this->meta_prefix . 'cc_sa', true ) )
					$html .= '<span class="cc cc-sa" title="' . __( 'Creative Commons Share Alike', 'gga-dynamic-placeholder-images' ) . '">' . $this->cc_img_html( '', 'cc_sa' ) . '</span>';

				if ( 'on' === get_post_meta( $id, $this->meta_prefix . 'cc_nc', true ) )
					$html .= '<span class="cc cc-nc" title="' . __( 'Creative Commons Non-Commercial', 'gga-dynamic-placeholder-images' ) . '">' . $this->cc_img_html( '', 'cc_nc' ) . '</span>';

				if ( $cc_url != '' )
					$html .= "<a class=\"some-rights-reserved\" href=\"{$cc_url}\" target=\"_blank\">" . __( 'Some rights reserved', 'gga-dynamic-placeholder-images' ) . "</a><br/>";

				$html .= '</div><!-- .attribImage -->';


			}



			$html .= '</div>';

			$itemwidth = $args['columns'] > 0 ? floor(100/$args['columns']) : 100;

			$html .= "<style>
				.gga-dynamic-images-attribution .attribImage { width: {$itemwidth}% }
			</style>";

			// let's the output be modified if-needed
			$html = apply_filters( $this->plugin_name . '-attribution-shortcode-html', $html, $posts );

			return $html;

		}


		function init_filesystem() {
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


		function get_cache_directory() {
			$cache_directory = apply_filters( $this->plugin_name . '-setting-get', 'gga-dynamic-placeholder-images', $this->plugin_name . '-settings-cache', 'cache-directory' );
			$upload_dir = wp_upload_dir();
			return path_join( $upload_dir['basedir'], $cache_directory );
		}


		function create_cache_directory() {
			$cache_directory = $this->get_cache_directory();
			if ( wp_mkdir_p( $cache_directory ) ) {
				foreach( $this->cache_width_directories() as $width ) {
					$this->create_cache_width_directory( $cache_directory, $width );
				}
			}

		}

		function cache_width_directories() {
			$widths = array();
			for ( $width = 0; $width <= 2000; $width += 100 ) {
				$widths[] = $width;
			}
			return $widths;
		}


		function create_cache_width_directory( $base_cache_directory, $width ) {
			wp_mkdir_p( path_join( $base_cache_directory, $width ) );
		}


		function delete_cache_directory() {

			if ( $this->init_filesystem() ) {
				global $wp_filesystem;
				$cache_directory = $this->get_cache_directory();
				delete_site_transient( $this->plugin_name . '-cache-size' );
				return $wp_filesystem->rmdir( $cache_directory, true );
			} else {
				return false;
			}

		}


		function purge_cache_directory() {
			if ( $this->delete_cache_directory() ) {
				return $this->create_cache_directory();
			}
			else {
				return false;
			}
		}

		function get_cache_directory_contents() {
			if ( $this->init_filesystem() ) {
				$cache_directory = $this->get_cache_directory();
				global $wp_filesystem;
				return $wp_filesystem->dirlist( $cache_directory, false, true );
			} else {
				return false;
			}
		}


		function get_cache_directory_size( $size ) {
			$transient = $this->plugin_name . '-cache-size';
			$size = get_site_transient( $transient );
			if ( ! empty( $size ) ) {
				return $size;
			}

			$list = $this->get_cache_directory_contents();
			if ( !empty( $list ) ) {
				$size = $this->get_directory_size( $list );
				set_site_transient( $transient, $size, MINUTE_IN_SECONDS * 15 );
				return $size;
			} else {
				return $size;
			}
		}


		function get_directory_size( $list ) {
			$size = 0;

			if ( ! empty( $list ) ) {

				foreach ($list as $key => $item) {
					if ( $item['type'] == 'f' ) {
						$size += $item['size'];
					} else if ( $item['type'] == 'd' && ! empty( $item['files'] ) ) {
						$size += $this->get_directory_size( $item['files'] );
					}
				}

			}

			return $size;
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


		function cc_img_html( $html, $cc_type ) {
			$filename = '';
			switch ( $cc_type ) {
				case 'cc_nc':
					$filename = 'cc-non-commercial.png';
					$alt = __( 'Creative Commons Non-Commercial', 'gga-dynamic-placeholder-images' );
					break;
				case 'cc_by':
					$filename = 'cc-attribution.png';
					$alt = __( 'Creative Commons Attribution', 'gga-dynamic-placeholder-images' );
					break;
				case 'cc_sa':
					$filename = 'cc-share-alike.png';
					$alt = __( 'Creative Commons Share Alike', 'gga-dynamic-placeholder-images' );
					break;
			}

			if ( ! empty( $filename ) ) {
				$html = '<img class="gga_' . $cc_type . '" src="' . $this->plugin_base_url . 'public/images/' . $filename . '" alt="' . esc_attr( $alt ) . '" />';
			}

			return $html;

		}



		// because Sneek read the code

	}


}
