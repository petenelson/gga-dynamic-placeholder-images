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

					<?php $this->dashboard_widget_cache(); ?>

				</div>

			<?php
		}

		public function dashboard_widget_cache() {
			$cache_size = size_format( intval( apply_filters( $this->plugin_name . '-get-cache-size', 0 ) ) );
			if ( empty ( $cache_size ) ) {
				$cache_size = __( 'Empty', 'gga-dynamic-placeholder-images' );
			}

			?>

				<ul>
					<li><?php _e( 'Current Cache Size', 'gga-dynamic-placeholder-images' ) ?>: <?php echo $cache_size; ?> </li>
				</ul>

				<?php
					$action = 'purge-cache';
					$url = add_query_arg( array(
						$this->plugin_name . '-action' => $action,
						$this->plugin_name . '-nonce' => wp_create_nonce( $action ),
					) );
				?>

				<a class="button" href="<?php echo $url ?>"><?php _e( 'Purge Cache', 'gga-dynamic-placeholder-images' ) ?></a>

			<?php
		}

	}
}
