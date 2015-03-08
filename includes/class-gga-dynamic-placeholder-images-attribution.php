<?php
if ( ! defined( 'ABSPATH' ) ) wp_die( 'restricted access' );

if ( ! class_exists( 'GGA_Dynamic_Placeholder_Images_Attribution' ) ) {

	class GGA_Dynamic_Placeholder_Images_Attribution {

		private $version = '2015-03-08-02';
		private $plugin_name = 'gga-dynamic-images';
		private $meta_prefix = '_gga_image_';

		var $plugin_base_url = '';
		var $plugin_base_dir = '';

		public function plugins_loaded() {

			// displays the image attribution list
			add_shortcode( 'dynamic-images-attribution', array( $this, 'image_attribution_shortcode' ) );

			// for generating Creative Commons icons
			add_filter( $this->plugin_name . '-cc-img-html', array( $this, 'cc_img_html' ), 10, 2 );

		}


		public function image_attribution_shortcode( $args ) {

			global $gga_attrib_args;

			wp_enqueue_style( $this->plugin_name . '-attribution', $this->plugin_base_url . 'public/css/gga-dynamic-images.css', array(), $this->version );
			$gga_attrib_args = $this->sanitize_args( wp_parse_args( $args, $this->default_shortcode_args() ) );

			ob_start();
			?>
				<div class="<?php echo esc_attr( $gga_attrib_args['class'] ); ?>">
					<?php
						$posts = $this->query_posts( );
						$this->echo_attribution_items_html( $posts );
					?>
				</div><!-- end of attribution images -->
				<style> .gga-dynamic-images-attribution .attribImage { max-width: <?php echo ( $gga_attrib_args['columns'] > 0 ? floor( 100 / $gga_attrib_args['columns'] ) : 100 ) - 1; ?>% } </style>
			<?php
			$html = ob_get_contents();
			ob_end_clean();

			// let's the output be modified if-needed
			return apply_filters( $this->plugin_name . '-attribution-shortcode-html', $html, $posts );
		}


		private function default_shortcode_args() {
			return array(
				'width' => 300,
				'height' => 300,
				'columns' => 3,
				'class' => 'gga-dynamic-images-attribution',
			);
		}


		private function echo_attribution_items_html( $posts ) {
			global $gga_image_post;
			foreach( $posts as $gga_image_post ) {
				include $this->plugin_base_dir . 'public/partials/image-attribution-item.php';
			}
		}


		public function cc_img_html( $html, $cc_type ) {

			$cc_data = array(
				'cc_nc' => array(
					'filename' => 'cc-non-commercial.png',
					'alt' => __( 'Creative Commons Non-Commercial', 'gga-dynamic-placeholder-images' ),
				),
				'cc_by' => array(
					'filename' => 'cc-attribution.png',
					'alt' => __( 'Creative Commons Attribution', 'gga-dynamic-placeholder-images' ),
				),
				'cc_sa' => array(
					'filename' => 'cc-share-alike.png',
					'alt' => __( 'Creative Commons Share Alike', 'gga-dynamic-placeholder-images' ),
				),
			);

			if ( ! empty( $cc_data[ $cc_type ] ) ) {
				$html = '<img class="gga_' . esc_attr( $cc_type ) . '" src="' . $this->plugin_base_url . 'public/images/' . $cc_data[ $cc_type ]['filename'] . '" alt="' . esc_attr( $cc_data[ $cc_type ]['alt'] ) . '" />';
			}

			return $html;
		}


		private function query_posts( ) {
			$core = new GGA_Dynamic_Placeholder_Images_Core();

			$query_args = $core->image_query_args();
			$query_args['posts_per_page'] = -1;
			$query_args['orderby'] = 'name';
			$query_args['order'] = 'asc';

			return $core->query_images( $query_args );
		}


		private function sanitize_args( $args ) {

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

			return $args;

		}


	}
}
