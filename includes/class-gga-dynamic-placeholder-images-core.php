
<?php

if ( !defined( 'ABSPATH' ) ) exit( 'restricted access' );

if ( ! class_exists( 'GGA_Dynamic_Placeholder_Images_Core' ) ) {

	class GGA_Dynamic_Placeholder_Images_Core {

		var $version = '2015-02-26-01';
		var $sizes;
		var $meta_prefix = '_gga_image_';
		var $options = '_gga_placeholder_image_options';
		var $plugin_name = 'gga-dynamic-images';
		var $add_expires = true;
		var $plugin_base_url = '';


		function plugins_loaded() {

			load_plugin_textdomain( 'gga-dynamic-placeholder-images' );

			add_action( 'init', array( $this, 'register_rewrites' ) );
			add_action( 'template_redirect', array( $this, 'template_redirect' ) );
			add_action( 'delete_attachment', array( $this, 'delete_attachment' ) );
			add_shortcode( 'gga-image-attribution', array( $this, 'image_attribution_shortcode' ) );

			if ( is_admin() ) {
				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
				add_action( 'admin_init', array( $this, 'admin_register_settings' ) );
			}

			// for generating Creative Commons icons
			add_filter( $this->plugin_name . '-cc-img-html', array( $this, 'cc_img_html' ), 10, 2 );

			// for getting an option value
			add_filter( $this->plugin_name . '-get-option', array( $this, 'get_option_filter'), 10, 2 );

		}


		function register_rewrites() {
			add_rewrite_tag( '%gga-dynamic-image%', '1' );
			add_rewrite_tag( '%gga-dynamic-image-width%', '([0-9]+)' );
			add_rewrite_tag( '%gga-dynamic-image-height%', '([0-9]+)' );
			add_rewrite_tag( '%gga-dynamic-image-slug%', '([A-Za-z0-9\-\_]+)' );

			add_rewrite_rule( $this->get_base_url() . '([0-9]+)/([0-9]+)/([A-Za-z0-9\-\_]+)/?', 'index.php?gga-dynamic-image=1&gga-dynamic-image-width=$matches[1]&gga-dynamic-image-height=$matches[2]&gga-dynamic-image-slug=$matches[3]', 'top' );
			add_rewrite_rule( $this->get_base_url() . '([0-9]+)/([0-9]+)/?', 'index.php?gga-dynamic-image=1&gga-dynamic-image-width=$matches[1]&gga-dynamic-image-height=$matches[2]&gga-dynamic-image-slug=random', 'top' );
		}

		function template_redirect() {

			global $wp_query;

			$action = $wp_query->get( 'gga-dynamic-image' );

			if ( ! empty( $action ) ) {
				$this->handle_dynamic_image();
			}

		}


		function get_option_filter( $value, $key ) {
			$options = get_option( $this->options );
			if ( ! empty( $options ) && isset( $options[ $key ] ) ) {
				return $options[ $key ];
			}
			else {
				return $value;
			}
		}


		function get_base_url() {
			$base_url = apply_filters( $this->plugin_name . '-get-option', 'dynamic-image', 'base_url' );
			return ! empty( $base_url ) ? $base_url . '/' : '';
		}


		function handle_dynamic_image() {

			global $wp_query;

			$width = intval( $wp_query->get( 'gga-dynamic-image-width' ) );
			$height = intval( $wp_query->get( 'gga-dynamic-image-height' ) );
			$slug = sanitize_key( $wp_query->get( 'gga-dynamic-image-slug' ) );


			if ( $width !== 0 && $height !== 0 ) {

				$this->load_image_sizes();
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

				if ( $id === 0 || $id === false ) {
					$id = $this->get_random_image_id();
				}


				if ( $id === 0 || $id === false || $id === NULL ) {
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


		function admin_register_settings() {
			$opt = $this->options;

			register_setting( $opt, $opt );

			$section = $opt . '_general';

			add_settings_section( $section, __( 'General', 'gga-dynamic-placeholder-images' ), array( $this, 'dynamic_image_settings_section' ), $opt );
			add_settings_field( 'title', __( 'Title:', 'gga-dynamic-placeholder-images' ), array( $this, 'dynamic_image_title' ), $opt, $section );
			add_settings_field( 'base_url', __( 'Base URL:', 'gga-dynamic-placeholder-images' ), array( $this, 'dynamic_image_base_url' ), $opt, $section );

		}

		function dynamic_image_settings_section() {}


		function dynamic_image_title() {
			$this->setting_input( 'title', 50, 50 );
		}


		function dynamic_image_base_url() {
			$this->setting_input( 'base_url', 50, 50 );
		}


		function setting_input( $name, $size, $maxlength, $classes = '' ) {
			$options = get_option( $this->options );
			$optionValue = isset( $options[$name] ) ? $options[$name] : "";

			echo "<input id='{$name}' name='" . $this->options . "[{$name}]' size='{$size}' maxlength='{$maxlength}' type='text' value='" . esc_attr( $optionValue ) . "' class=\"{$classes}\" />";
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
			$fullsizepath = get_attached_file( $id );

			$dirname = pathinfo( $fullsizepath );

			$meta = wp_get_attachment_metadata( $id );
			$sizes = $meta['sizes'];

			if ( $sizes[$image_size_name] ) {

				// log some stats
				$this->log_image_view( $w, $h );


				$filename = path_join( $dirname['dirname'], $sizes[$image_size_name]['file'] );
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
				die();

			}
			else {
				$this->show_404_and_die();
			}

		}

		function log_image_view( $w, $h ) {

			$size = "{$w}-{$h}";
			$key = '_gga_placeholder_image_views_for_'  . gmdate( 'Y-m-d', time() );
			$opt = get_option( $key );
			$new = false;
			if ( false === $opt ) {
				$opt = array();
				$new = true;
			}

			if ( !isset( $opt[$size] ) )
				$opt[$size] = 0;

			$opt[$size]++;

			if ( $new )
				add_option( $key, $opt, $deprecated = '', $autoload = 'no' );
			else
				update_option( $key, $opt );

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
			$this->save_images_sizes();

			add_image_size( $image_size_name, $w, $h, true );

		}



		function generate_image( $id, $w, $h ) {


			$image_size_name = $this->image_size_name( $w, $h );

			$this->add_image_size( $w, $h );

			$exists = $this->image_size_exists( $id, $w, $h );



			if ( !$exists ) {
				$image = get_post( $id );
				if ( $image ) {
					$fullsizepath = get_attached_file( $image->ID );
					include_once ABSPATH . 'wp-admin/includes/image.php';

					// don't call wp_generate_attachment_metadata because it regenerates existing images
					$metadata = wp_get_attachment_metadata( $id );

					$resized =  image_make_intermediate_size( $fullsizepath, $w, $h, $crop=true );
					if ( false !== $resized ) {
						$metadata['sizes'][$image_size_name] = $resized;
						wp_update_attachment_metadata( $id, $metadata );
					}
				}

			}


			// save option so we know which image to use for the size
			if ( false === $this->get_existing_image_id_by_dimensions( $w, $h ) )
				add_option( "_gga-placeholder-image-for-{$w}-{$h}", $id, '', 'no' );


		}

		function get_existing_image_id_by_dimensions( $w, $h ) {
			return get_option( "_gga-placeholder-image-for-{$w}-{$h}", false );
		}



		function image_size_exists( $id, $w, $h ) {
			$meta = wp_get_attachment_metadata( $id );
			$sizes = $meta['sizes'];
			return isset( $sizes[$this->image_size_name( $w, $h )] );
		}


		function image_size_name( $w, $h ) {
			return 'gga-image-' . $w . '-' . $h;
		}


		function image_attribution_shortcode() {

			wp_enqueue_style( $this->plugin_name . '-attribution', $this->plugin_base_url . 'public/css/gga-dynamic-images.css', array(), $this->version );

			$html = '<div class="gga-dynamic-images-attribution">';

			$args = $this->image_query_args();
			$args['posts_per_page'] = -1;
			$args['orderby'] = 'name';
			$args['order'] = 'asc';

			$posts = $this->query_images( $args );

			foreach( $posts as $post ) {

				$id = $post->ID;

				$tag = $post->post_name;
				$image_url = site_url( $this->get_base_url() . '200/200/' . $tag );
				$cc_url = '';
				$attrib_to = get_post_meta( $id, $this->meta_prefix . 'attribute_to', true );
				$attrib_url = get_post_meta( $id, $this->meta_prefix . 'attribute_url', true );

				if ( 'on' === get_post_meta( $id, $this->meta_prefix . 'cc_by', true ) );
					$cc_url = 'http://creativecommons.org/licenses/by/2.0/';

				if ( 'on' === get_post_meta( $id, $this->meta_prefix . 'cc_sa', true ) );
					$cc_url = 'http://creativecommons.org/licenses/by-sa/2.0/';

				$html .= "
				<div class=\"attribImage\">
					<a class=\"meat\" href=\"{$image_url}\"><img class=\"meat\" src=\"{$image_url}\" alt=\"\" width=\"200\" height=\"200\" /></a>
					tag: {$tag}<br/>";

				if ( 'on' === get_post_meta( $id, $this->meta_prefix . 'cc_by', true ) )
					$html .= '<span class="cc cc-by" title="' . __( 'Creative Commons Attribution', 'gga-dynamic-placeholder-images' ) . '">' . $this->cc_img_html( '', 'cc_by' ) . '</span>';

				if ( 'on' === get_post_meta( $id, $this->meta_prefix . 'cc_sa', true ) )
					$html .= '<span class="cc cc-sa" title="' . __( 'Creative Commons Share Alike', 'gga-dynamic-placeholder-images' ) . '">' . $this->cc_img_html( '', 'cc_sa' ) . '</span>';

				if ( 'on' === get_post_meta( $id, $this->meta_prefix . 'cc_nc', true ) )
					$html .= '<span class="cc cc-nc" title="' . __( 'Creative Commons Non-Commercial', 'gga-dynamic-placeholder-images' ) . '">' . $this->cc_img_html( '', 'cc_nc' ) . '</span>';

				if ( $cc_url != '' )
					$html .= "<a href=\"{$cc_url}\" target=\"_blank\">" . __( 'Some rights reserved', 'gga-dynamic-placeholder-images' ) . "</a><br/>";

				if ( $attrib_to !== false && !empty( $attrib_to ) )
					$html .= "by <a href=\"{$attrib_url}\" target=\"_blank\">" . htmlspecialchars( $attrib_to ) . "</a>";

				$html .= '</div><!-- .attribImage -->';


			}



			$html .= '<div class="clear"></div>
			</div><!-- #attribImages -->';


			wp_reset_postdata();

			return $html;

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
