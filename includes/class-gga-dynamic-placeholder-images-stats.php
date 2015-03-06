<?php
if ( ! defined( 'ABSPATH' ) ) wp_die( 'restricted access' );

if ( ! class_exists( 'GGA_Dynamic_Placeholder_Images_Stats' ) ) {

	class GGA_Dynamic_Placeholder_Images_Stats {

		private $plugin_name = 'gga-dynamic-images';
		private $version = '2015-03-03-05';


		public function plugins_loaded() {

			$this->update_db_check();

			add_action( $this->plugin_name . '-image-view', array( $this, 'log_image_view' ) );

		}


		public function activation_hook() {
			$this->create_stats_table();
		}


		function update_db_check() {

			if ( get_site_option( $this->plugin_name . '-stats-version' ) != $this->version ) {
				$this->create_stats_table();
			}

		}


		function stats_table_name() {
			global $wpdb;
			return $wpdb->prefix . 'gga_dynamic_images_stats';
		}


		function create_stats_table() {
			// for reference
			// You must put each field on its own line in your SQL statement.
			// You must have two spaces between the words PRIMARY KEY and the definition of your primary key.
			// You must use the key word KEY rather than its synonym INDEX and you must include at least one KEY.
			// You must not use any apostrophes or backticks around field names.
			// Field types must be all lowercase.
			// SQL keywords, like CREATE TABLE and UPDATE, must be uppercase.

			global $wpdb;
			$table_name = $this->stats_table_name();

			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
			  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			  time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  post_id bigint(20) UNSIGNED NOT NULL,
			  width int(11) UNSIGNED NOT NULL,
			  height int(11) UNSIGNED NOT NULL,
			  bytes int(11) UNSIGNED NOT NULL,
			  PRIMARY KEY  (id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

			update_site_option( $this->plugin_name . '-stats-version', $this->version );

		}


		function log_image_view( $args ) {

			$args = wp_parse_args( $args, array(
				'post_id' => 0,
				'width' => 0,
				'height' => 0,
				'bytes' => 0,
			));

			global $wpdb;
			$table_name = $this->stats_table_name();

			$wpdb->insert(
				$table_name,
				array(
					'time' => current_time( 'mysql' ),
					'post_id' => intval( $args['post_id'] ),
					'width' => intval( $args['width'] ),
					'height' => intval( $args['height'] ),
					'bytes' => intval( $args['bytes'] ),
				)
			);

			$error = $wpdb->last_error;

			if ( strlen( trim( $error ) ) > 0 ) {
				$this->create_stats_table();
			}


		}

	}


}