<?php

$image_url = apply_filters( 'gga-dynamic-images-image-url', '', 300, 300, 'random' );

?>
<p class="section-header">
	View a random image: <a target="_blank" href="<?php echo $image_url ?>"><?php echo $image_url ?></a>
</p>