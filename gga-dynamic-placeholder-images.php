<?php
/*
 * Plugin Name: Dynamic Placeholder Images
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

if ( ! defined( 'ABSPATH' ) ) wp_die( 'restricted access' );

$includes = array( 'core', 'cache', 'api', 'attachment-meta', 'settings', 'dashboard-widgets', 'stats', 'attribution' );
foreach ($includes as $include) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gga-dynamic-placeholder-images-' . $include . '.php';
}


// handles URL rewrites, generating placeholder images, flushing cache, etc
if ( class_exists( 'GGA_Dynamic_Placeholder_Images_Core' ) ) {
	$gga_dynamic_placeholder_images_core = new GGA_Dynamic_Placeholder_Images_Core();
	$gga_dynamic_placeholder_images_core->plugin_base_url = plugin_dir_url( __FILE__ );
	add_action( 'plugins_loaded', array( $gga_dynamic_placeholder_images_core, 'plugins_loaded' ) );
}


// Cache management
if ( class_exists( 'GGA_Dynamic_Placeholder_Images_Cache' ) ) {
	$gga_placeholder_images_cache = new GGA_Dynamic_Placeholder_Images_Cache();
	add_action( 'plugins_loaded', array( $gga_placeholder_images_cache, 'plugins_loaded' ) );
}


// exposes an API endpoint and handles API actions
if ( class_exists( 'GGA_Dynamic_Placeholder_Images_API' ) ) {
	$gga_dynamic_placeholder_images_api = new GGA_Dynamic_Placeholder_Images_API();
	add_action( 'plugins_loaded', array( $gga_dynamic_placeholder_images_api, 'plugins_loaded' ) );
}


// adds meta fields to attachments
if ( class_exists( 'GGA_Dynamic_Placeholder_Images_Attachment_Meta' ) ) {
	$gga_dynamic_placeholder_images_attachment_meta = new GGA_Dynamic_Placeholder_Images_Attachment_Meta();
	$gga_dynamic_placeholder_images_attachment_meta->plugin_base_url = plugin_dir_url( __FILE__ );
	add_action( 'plugins_loaded', array( $gga_dynamic_placeholder_images_attachment_meta, 'plugins_loaded' ) );
}


// handles Admin Settings pages and filters to get plugin settings
if ( class_exists( 'GGA_Dynamic_Placeholder_Images_Settings' ) ) {
	$gga_dynamic_placeholder_images_settings = new GGA_Dynamic_Placeholder_Images_Settings();
	$gga_dynamic_placeholder_images_settings->plugin_base_dir = plugin_dir_path( __FILE__ );
	add_action( 'plugins_loaded', array( $gga_dynamic_placeholder_images_settings, 'plugins_loaded' ) );
}


// Dashboard widgets
if ( class_exists( 'GGA_Dynamic_Placeholder_Images_Dashboard_Widgets' ) ) {
	$gga_dynamic_placeholder_images_dashboard_widgets = new GGA_Dynamic_Placeholder_Images_Dashboard_Widgets();
	add_action( 'plugins_loaded', array( $gga_dynamic_placeholder_images_dashboard_widgets, 'plugins_loaded' ) );
}


// Stats logging
if ( class_exists( 'GGA_Dynamic_Placeholder_Images_Stats' ) ) {
	$gga_dynamic_placeholder_images_stats = new GGA_Dynamic_Placeholder_Images_Stats();
	add_action( 'plugins_loaded', array( $gga_dynamic_placeholder_images_stats, 'plugins_loaded' ) );
	register_activation_hook( __FILE__, array( $gga_dynamic_placeholder_images_stats, 'activation_hook' ) );
}


// Attribution shortcode
if ( class_exists( 'GGA_Dynamic_Placeholder_Images_Attribution' ) ) {
	$gga_dynamic_placeholder_images_attribution = new GGA_Dynamic_Placeholder_Images_Attribution();
	$gga_dynamic_placeholder_images_attribution->plugin_base_url = plugin_dir_url( __FILE__ );
	$gga_dynamic_placeholder_images_attribution->plugin_base_dir = plugin_dir_path( __FILE__ );
	add_action( 'plugins_loaded', array( $gga_dynamic_placeholder_images_attribution, 'plugins_loaded' ) );
}

