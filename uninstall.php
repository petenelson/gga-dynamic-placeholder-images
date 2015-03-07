<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) wp_die( 'restricted access' );

// delete stats table
global $wpdb;
$table_name = $wpdb->prefix . 'gga_dynamic_images_stats';
$sql = "DROP TABLE IF_EXISTS $table_name";
$wpdb->query($sql);

$sql = "DELETE FROM $wpdb->options WHERE option_name like '_gga-placeholder-image-for%'"
$wpdb->query($sql);
