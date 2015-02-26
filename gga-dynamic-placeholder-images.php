<?php
/*
 * Plugin Name: GGA Dynamic Placeholder Images
 * Plugin URI: https://github.com/petenelson/gga-dynamic-placeholder-images
 * Description: Plugin for managing and serving up placeholder images (such as <a href="http://baconmockup.com/200/200" target="_blank">http://baconmockup.com/200/200</a>)
 * Version: 2.0
 * Author: Pete Nelson (@GunGeekATX)
 * Author URI: https://twitter.com/GunGeekATX
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 */

if ( !defined( 'ABSPATH' ) ) exit( 'restricted access' );

$includes = array( 'core',  'api' , 'admin' );
foreach ($includes as $include) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gga-dynamic-placeholder-images-' . $include . '.php';
}


if ( class_exists( 'GGA_Dynamic_Placeholder_Images_Core' ) ) {
	$gga_dynamic_placeholder_images_core = new GGA_Dynamic_Placeholder_Images_Core();
	$gga_dynamic_placeholder_images_core->plugin_base_url = plugin_dir_url( __FILE__ );
	add_action( 'plugins_loaded', array( $gga_dynamic_placeholder_images_core, 'plugins_loaded' ) );
}


if ( class_exists( 'GGA_Dynamic_Placeholder_Images_API' ) ) {
	$gga_dynamic_placeholder_images_api = new GGA_Dynamic_Placeholder_Images_API();
	add_action( 'plugins_loaded', array( $gga_dynamic_placeholder_images_api, 'plugins_loaded' ) );
}


if ( class_exists( 'GGA_Dynamic_Placeholder_Images_Admin' ) ) {
	$gga_dynamic_placeholder_images_admin = new GGA_Dynamic_Placeholder_Images_Admin();
	$gga_dynamic_placeholder_images_admin->plugin_base_url = plugin_dir_url( __FILE__ );
	add_action( 'plugins_loaded', array( $gga_dynamic_placeholder_images_admin, 'plugins_loaded' ) );
}
