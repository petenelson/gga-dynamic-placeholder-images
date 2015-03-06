<?php
if ( ! defined( 'ABSPATH' ) ) wp_die( 'restricted access' );

if ( ! class_exists( 'GGA_Dynamic_Placeholder_Images_Dashboard_Widgets' ) ) {

	class GGA_Dynamic_Placeholder_Images_Dashboard_Widgets {

		private $plugin_name = 'gga-dynamic-images';

		public function plugins_loaded() {
			add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widgets' ) );
		}


		function register_dashboard_widgets() {

			if ( current_user_can( 'manage_options' ) ) {

				wp_add_dashboard_widget( $this->plugin_name . '-dashboard-widget',
					__('Dynamic Placeholder Images', 'gga-dynamic-placeholder-images' ),
					array( $this, 'dashboard_widget' )
				);

			}

		}


		public function dashboard_widget() {

			?>
				<div class="inside">

					<?php $this->dashboard_widget_cache( $show_help = true ); ?>

				</div>

			<?php
		}

		public function dashboard_widget_cache( $show_help = false ) {

			$cache_size = size_format( intval( apply_filters( $this->plugin_name . '-get-cache-size', 0 ) ) );
			if ( empty ( $cache_size ) ) {
				$cache_size = __( 'Empty', 'gga-dynamic-placeholder-images' );
			}

			$image_associations = number_format( intval( apply_filters( $this->plugin_name . '-get-associations-count', 0 ) ) );

			?>

				<ul>
					<li><?php _e( 'Current Cache Size', 'gga-dynamic-placeholder-images' ) ?>: <?php echo $cache_size; ?> </li>
					<li><?php _e( 'Image Association Count', 'gga-dynamic-placeholder-images' ) ?>: <?php echo $image_associations; ?> </li>
				</ul>

				<?php

				if ( $show_help ) { ?>
					<p>
						<a href="<?php echo admin_url( 'options-general.php?page=gga-dynamic-images-settings&tab=gga-dynamic-images-settings-help#gga-help-cache' ); ?>"><?php _e( 'Help' ) ?></a>
					</p>

					<?php
				}
				?>

				<?php
					$action = 'purge-cache';
					$url = add_query_arg( array(
						$this->plugin_name . '-action' => $action,
						$this->plugin_name . '-nonce' => wp_create_nonce( $action ),
					) );
				?>

				<a class="button" href="<?php echo $url ?>"><?php _e( 'Purge Cache', 'gga-dynamic-placeholder-images' ) ?></a>

				<?php
					$action = 'delete-associations';
					$url = add_query_arg( array(
						$this->plugin_name . '-action' => $action,
						$this->plugin_name . '-nonce' => wp_create_nonce( $action ),
					) );
				?>

				<a class="button" href="<?php echo $url ?>"><?php _e( 'Purge Image Associations', 'gga-dynamic-placeholder-images' ) ?></a>

			<?php
		}

	}
}
