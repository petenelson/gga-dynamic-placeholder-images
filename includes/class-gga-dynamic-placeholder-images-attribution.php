<?php
if ( ! defined( 'ABSPATH' ) ) wp_die( 'restricted access' );

if ( ! class_exists( 'GGA_Dynamic_Placeholder_Images_Attribution' ) ) {

	class GGA_Dynamic_Placeholder_Images_Attribution {

		private $version = '2015-03-06-01';
		private $plugin_name = 'gga-dynamic-images';
		private $meta_prefix = '_gga_image_';

		var $plugin_base_url = '';

		public function plugins_loaded() {

			// displays the image attribution list
			add_shortcode( 'gga-image-attribution', array( $this, 'image_attribution_shortcode' ) );

			// for generating Creative Commons icons
			add_filter( $this->plugin_name . '-cc-img-html', array( $this, 'cc_img_html' ), 10, 2 );

		}


		function image_attribution_shortcode( $args ) {

			wp_enqueue_style( $this->plugin_name . '-attribution', $this->plugin_base_url . 'public/css/gga-dynamic-images.css', array(), $this->version );

			$args = wp_parse_args( $args, $this->default_shortcode_args() );


			$args = $this->sanitize_args( $args );

			ob_start();
			?>

			<div class="<?php echo $args['class']; ?>">
				<?php
					$posts = $this->query_posts( );
					$this->echo_attribution_items_html( $posts, $args );
				?>
			</div><!-- end of attribution images -->

			<style> .gga-dynamic-images-attribution .attribImage { max-width: <?php echo ( $args['columns'] > 0 ? floor( 100 / $args['columns'] ) : 100 ) - 1; ?>% } </style>
			<?php

			$html = ob_get_contents();
			ob_end_clean();

			// let's the output be modified if-needed
			$html = apply_filters( $this->plugin_name . '-attribution-shortcode-html', $html, $posts );

			return $html;

		}


		private function default_shortcode_args() {
			return array(
				'width' => 300,
				'height' => 300,
				'columns' => 3,
				'class' => 'gga-dynamic-images-attribution',
			);
		}


		private function echo_attribution_items_html( $posts, $args ) {
			foreach( $posts as $post ) {

				$attrib_to = get_post_meta( $post->ID, $this->meta_prefix . 'attribute_to', true );
				$attrib_url = get_post_meta( $post->ID, $this->meta_prefix . 'attribute_url', true );

				$cc_url = '';

				if ( 'on' === get_post_meta( $post->ID, $this->meta_prefix . 'cc_by', true ) );
					$cc_url = 'http://creativecommons.org/licenses/by/2.0/';

				if ( 'on' === get_post_meta( $post->ID, $this->meta_prefix . 'cc_sa', true ) );
					$cc_url = 'http://creativecommons.org/licenses/by-sa/2.0/';

				$this->echo_attribution_item_html( $post, $args, $cc_url, $attrib_to, $attrib_url );

			} // end foreach $posts

		}


		private function echo_attribution_item_html( $post, $args, $cc_url, $attrib_to, $attrib_url ) {
			$image_url = apply_filters( $this->plugin_name . '-image-url', '', $args['width'], $args['height'], $post->post_name );
			?>
			<div class="attribImage">
				<div class="attribImage-inner">
					<a class="image-link" href="<?php echo $image_url; ?>"><img class="image-thumbnail" src="<?php echo $image_url; ?>" alt="<?php echo esc_attr( $post->post_name ); ?>" width="<?php echo $args['width']; ?>" height="<?php echo $args['height']; ?>" /></a>
					<div class="image-meta">
						<div class="image-tag">tag: <?php echo esc_html( $post->post_name ); ?></div>
						<?php if ( $attrib_to !== false && !empty( $attrib_to ) ) { ?><div class="attribute-to">by <a href="<?php echo $attrib_url ?>" target="_blank"><?php echo esc_html( $attrib_to ); ?></a></div><?php } ?>
						<?php if ( 'on' === get_post_meta( $post->ID, $this->meta_prefix . 'cc_by', true ) ) { ?><span class="cc cc-by" title="<?php _e( 'Creative Commons Attribution', 'gga-dynamic-placeholder-images' ); ?>"><?php echo $this->cc_img_html( '', 'cc_by' ); ?></span><?php } ?>
						<?php if ( 'on' === get_post_meta( $post->ID, $this->meta_prefix . 'cc_sa', true ) ) { ?><span class="cc cc-sa" title="<?php _e( 'Creative Commons Share Alike', 'gga-dynamic-placeholder-images' ); ?>"><?php echo $this->cc_img_html( '', 'cc_sa' ); ?></span><?php } ?>
						<?php if ( 'on' === get_post_meta( $post->ID, $this->meta_prefix . 'cc_nc', true ) ) { ?><span class="cc cc-nc" title="<?php _e( 'Creative Commons Non-Commercial', 'gga-dynamic-placeholder-images' ); ?>"><?php echo $this->cc_img_html( '', 'cc_nc' ); ?></span><?php } ?>
						<?php if ( $cc_url != '' ) { ?><a class="some-rights-reserved" href="<?php echo $cc_url ?>" target="_blank"><?php _e( 'Some rights reserved', 'gga-dynamic-placeholder-images' ); ?></a><?php } ?>
					</div>
				</div>
			</div><!-- .attribImage -->
			<?php
		}


		public function cc_img_html( $html, $cc_type ) {
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
