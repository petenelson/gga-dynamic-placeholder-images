<?php
/*
Plugin Name: Dynamic Placeholder Images
Plugin URI: https://wordpress.org/plugins/dynamic-placeholder-images/
Description: Plugin for managing and serving up placeholder images (such as <a href="http://baconmockup.com/200/200" target="_blank">http://baconmockup.com/200/200</a>)
Version: 2.0.2
Author: Pete Nelson (@GunGeekATX)
Author URI: https://twitter.com/GunGeekATX
Text Domain: gga-dynamic-placeholder-images
Domain Path: /lang
*/

if ( ! defined( 'ABSPATH' ) ) wp_die( 'restricted access' );

$includes = array( 'core', 'cache', 'api', 'attachment-meta', 'settings', 'dashboard-widgets', 'stats', 'attribution' );
foreach ($includes as $include) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gga-dynamic-placeholder-images-' . $include . '.php';
}


// handles URL rewrites, generating placeholder images, flushing cache, etc
if ( class_exists( 'GGA_Dynamic_Placeholder_Images_Core' ) ) {
	$gga_images_core = new GGA_Dynamic_Placeholder_Images_Core();
	$gga_images_core->plugin_base_url = plugin_dir_url( __FILE__ );
	add_action( 'plugins_loaded', array( $gga_images_core, 'plugins_loaded' ) );
}


// Cache management
if ( class_exists( 'GGA_Dynamic_Placeholder_Images_Cache' ) ) {
	$gga_images_cache = new GGA_Dynamic_Placeholder_Images_Cache();
	add_action( 'plugins_loaded', array( $gga_images_cache, 'plugins_loaded' ) );
}


// exposes an API endpoint and handles API actions
if ( class_exists( 'GGA_Dynamic_Placeholder_Images_API' ) ) {
	$gga_images_api = new GGA_Dynamic_Placeholder_Images_API();
	add_action( 'plugins_loaded', array( $gga_images_api, 'plugins_loaded' ) );
}


// adds meta fields to attachments
if ( class_exists( 'GGA_Dynamic_Placeholder_Images_Attachment_Meta' ) ) {
	$gga_images_meta = new GGA_Dynamic_Placeholder_Images_Attachment_Meta();
	$gga_images_meta->plugin_base_url = plugin_dir_url( __FILE__ );
	add_action( 'plugins_loaded', array( $gga_images_meta, 'plugins_loaded' ) );
}


// handles Admin Settings pages and filters to get plugin settings
if ( class_exists( 'GGA_Dynamic_Placeholder_Images_Settings' ) ) {
	$gga_images_settings = new GGA_Dynamic_Placeholder_Images_Settings();
	$gga_images_settings->plugin_base_dir = plugin_dir_path( __FILE__ );
	add_action( 'plugins_loaded', array( $gga_images_settings, 'plugins_loaded' ) );
	register_activation_hook( __FILE__, array( $gga_images_settings, 'activation_hook' ) );
	register_deactivation_hook( __FILE__, array( $gga_images_settings, 'deactivation_hook' ) );
}


// Dashboard widgets
if ( class_exists( 'GGA_Dynamic_Placeholder_Images_Dashboard_Widgets' ) ) {
	$gga_images_widgets = new GGA_Dynamic_Placeholder_Images_Dashboard_Widgets();
	add_action( 'plugins_loaded', array( $gga_images_widgets, 'plugins_loaded' ) );
}


// Stats logging
if ( class_exists( 'GGA_Dynamic_Placeholder_Images_Stats' ) ) {
	$gga_images_stats = new GGA_Dynamic_Placeholder_Images_Stats();
	add_action( 'plugins_loaded', array( $gga_images_stats, 'plugins_loaded' ) );
	register_activation_hook( __FILE__, array( $gga_images_stats, 'activation_hook' ) );
}


// Attribution shortcode
if ( class_exists( 'GGA_Dynamic_Placeholder_Images_Attribution' ) ) {
	$gga_images_attrib = new GGA_Dynamic_Placeholder_Images_Attribution();
	$gga_images_attrib->plugin_base_url = plugin_dir_url( __FILE__ );
	$gga_images_attrib->plugin_base_dir = plugin_dir_path( __FILE__ );
	add_action( 'plugins_loaded', array( $gga_images_attrib, 'plugins_loaded' ) );
}

