<?php
if ( ! defined( 'ABSPATH' ) || ! current_user_can( 'manage_options' ) ) {
	wp_die('restricted access');
}

if ( class_exists( 'GGA_Dynamic_Placeholder_Images_Dashboard_Widgets' ) ) {
	$widget = new GGA_Dynamic_Placeholder_Images_Dashboard_Widgets();
	$widget->dashboard_widget_cache();
}

