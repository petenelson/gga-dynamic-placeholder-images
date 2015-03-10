<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	wp_die( 'restricted access' );
}

$option_keys = array( 'gga-dynamic-images-settings-general', 'gga-dynamic-images-settings-api', 'gga-dynamic-images-stats-version' );

foreach ( $option_keys as $key ) {
	delete_option( $key );
}

// delete stats table
global $wpdb;
$table_name = $wpdb->prefix . 'gga_dynamic_images_stats';
$sql = "DROP TABLE IF EXISTS $table_name";
$wpdb->query($sql);

$sql = "DELETE FROM $wpdb->options WHERE option_name like '_gga-placeholder-image-for%'";
$wpdb->query($sql);


flush_rewrite_rules();
