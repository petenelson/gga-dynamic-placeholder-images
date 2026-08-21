<?php
if (!defined( 'ABSPATH' )) die('restricted access');

$image_url = apply_filters( 'gga-dynamic-images-image-url', '', 300, 300, 'random' );

?>
<p class="section-header">
	View a random image: <a target="_blank" href="<?php echo esc_url( $image_url ) ?>"><?php echo esc_html( $image_url ) ?></a>
</p>