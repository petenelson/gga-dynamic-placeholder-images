<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit();

// delete stats table
global $wpdb;
$table_name = $wpdb->prefix . 'gga_dynamic_images_stats';
$sql = "DROP TABLE IF_EXISTS $table_name";
$wpdb->query($sql);

delete_option('gga-dynamic-image-sizes');
