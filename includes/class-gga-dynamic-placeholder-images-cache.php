<?php
if ( ! defined( 'ABSPATH' ) ) wp_die( 'restricted access' );

if ( ! class_exists( 'GGA_Dynamic_Placeholder_Images_Cache' ) ) {

	class GGA_Dynamic_Placeholder_Images_Cache {

		private $plugin_name = 'gga-dynamic-images';

		public function plugins_loaded() {

			add_filter( $this->plugin_name . '-get-cache-directory', array( $this, 'get_cache_directory' ) );
			add_filter( $this->plugin_name . '-get-cache-directory-contents', array( $this, 'get_cache_directory_contents' ) );

		}

		private function init_filesystem() {
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


		public function get_cache_directory( $cache_directory = '' ) {
			$cache_directory = apply_filters( $this->plugin_name . '-setting-get', 'gga-dynamic-placeholder-images', $this->plugin_name . '-settings-cache', 'cache-directory' );
			$upload_dir = wp_upload_dir();
			$cache_directory = path_join( $upload_dir['basedir'], $cache_directory );
			return $cache_directory;
		}

		public function get_cache_directory_contents( $contents = false ) {
			if ( $this->init_filesystem() ) {
				$cache_directory = $this->get_cache_directory();
				global $wp_filesystem;
				$contents = $wp_filesystem->dirlist( $cache_directory, false, true );
			}
			return $contents;
		}



	}

}