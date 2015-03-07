<?php
if (!defined( 'ABSPATH' )) wp_die('restricted access');

$enabled = apply_filters( $this->plugin_name . '-setting-is-enabled', 'images-api', $this->plugin_name . '-settings-api', 'api-enabled' );
$base_endpoint = apply_filters( $this->plugin_name . '-setting-get', 'images-api', $this->plugin_name . '-settings-api', 'api-endpoint' );

if ( ! empty( $enabled ) && ! empty( $base_endpoint ) ) {
	$url = site_url( trailingslashit( $base_endpoint ) );
}

?>

<p>
	<?php _e( 'Allows for a JSON API to your image generator.', 'gga-dynamic-placeholder-images' ); ?>
	<?php if ( ! empty( $url ) ) { ?>
		<br/><?php _e( 'View API Details', 'gga-dynamic-placeholder-images') ?>: <a href="<?php echo $url ?>" target="_blank"><?php echo $url ?></a>
	<?php } ?>
</p>
