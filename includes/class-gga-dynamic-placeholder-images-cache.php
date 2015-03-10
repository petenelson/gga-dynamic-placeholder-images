<?php
if ( ! defined( 'ABSPATH' ) ) wp_die( 'restricted access' );

if ( ! class_exists( 'GGA_Dynamic_Placeholder_Images_Cache' ) ) {

	class GGA_Dynamic_Placeholder_Images_Cache {

		private $plugin_name = 'gga-dynamic-images';

		public function plugins_loaded() {

			// allows cache interaction
			add_filter( $this->plugin_name . '-get-cache-directory', array( $this, 'get_cache_directory' ) );
			add_filter( $this->plugin_name . '-get-cache-directory-contents', array( $this, 'get_cache_directory_contents' ) );
			add_filter( $this->plugin_name . '-get-cache-size', array( $this, 'get_cache_directory_size' ) );

			add_action( $this->plugin_name . '-purge-cache', array( $this, 'purge_cache_directory' ) );

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


		public function get_cache_directory_size( $size ) {
			$transient = $this->plugin_name . '-cache-size';
			$size = get_site_transient( $transient );
			if ( ! empty( $size ) ) {
				return $size;
			}

			$list = $this->get_cache_directory_contents();
			if ( ! empty( $list ) ) {
				$size = $this->get_directory_size( $list );
				set_site_transient( $transient, $size, MINUTE_IN_SECONDS * 15 );
				return $size;
			} else {
				return $size;
			}

		}


		public function purge_cache_directory() {
			if ( $this->delete_cache_directory() ) {
				return $this->create_cache_directory();
			}
			else {
				return false;
			}
		}


		private function create_cache_directory() {
			$cache_directory = $this->get_cache_directory();
			if ( wp_mkdir_p( $cache_directory ) ) {
				foreach( $this->cache_width_directories() as $width ) {
					$this->create_cache_width_directory( $cache_directory, $width );
				}
			}

		}


		private function create_cache_width_directory( $base_cache_directory, $width ) {
			wp_mkdir_p( path_join( $base_cache_directory, $width ) );
		}


		public function delete_cache_directory() {

			if ( $this->init_filesystem() ) {
				global $wp_filesystem;
				$cache_directory = $this->get_cache_directory();
				delete_site_transient( $this->plugin_name . '-cache-size' );
				return $wp_filesystem->rmdir( $cache_directory, true );
			} else {
				return false;
			}

		}


		private function get_directory_size( $list ) {
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


		private function cache_width_directories() {
			$widths = array();
			for ( $width = 0; $width <= 2000; $width += 100 ) {
				$widths[] = $width;
			}
			return $widths;
		}



	}

}