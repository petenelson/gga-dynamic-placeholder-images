<?php
if ( ! defined( 'ABSPATH' ) ) wp_die( 'restricted access' );

$enabled = apply_filters( $this->plugin_name . '-setting-is-enabled', 'images-api', $this->plugin_name . '-settings-api', 'api-enabled' );
$base_endpoint = apply_filters( $this->plugin_name . '-setting-get', 'images-api', $this->plugin_name . '-settings-api', 'api-endpoint' );

if ( ! empty( $enabled ) && ! empty( $base_endpoint ) ) {
	$url = site_url( trailingslashit( $base_endpoint ) );
}


?>
<style>
	.gga-dynamic-images-placeholder-help p {
		margin-bottom: 2em;
	}
	.gga-dynamic-images-placeholder-help .indent {
		text-indent: 2em;
	}
	ul {
		margin-top: 0;
	}
</style>

<div class="gga-dynamic-images-placeholder-help">

	<h3 class="title"><?php _e( 'Using Images', 'gga-dynamic-placeholder-images' ); ?></h3>
	<p>
		<?php _e( 'We recommend upscaling your images to a large size, such as 2000x2000, before uploading them to WordPress.', 'gga-dynamic-placeholder-images' ) ?> <?php _e( 'Requesting a large version of a small placeholder image can result in a 404 error.', 'gga-dynamic-placeholder-images' ); ?>
	</p>

	<p>
		<?php _e( 'After your image is uploaded, you can flag it to be used as a dynamic image and fill in any appropriate Create Commons settings.', 'gga-dynamic-placeholder-images' ); ?>
		<?php _e( 'The image slug can be used as the tag in the URL to request a specific image.', 'gga-dynamic-placeholder-images' ); ?>
		<br/>
		<img src="<?php echo plugin_dir_url( __FILE__ ) ?>image-meta-01.png" alt="<?php _e( 'Image meta', 'gga-dynamic-placeholder-images' ); ?>" />
	</p>


	<h3 class="title"><?php _e( 'Shortcode', 'gga-dynamic-placeholder-images' ); ?></h3>
	<p>
		<?php _e( 'Display a list of images, their tags, and any Create Commons attribution', 'gga-dynamic-placeholder-images' ); ?>: <strong>[dynamic-images-attribution]</strong><br/>

		<br/>
		<strong><?php _e( 'Parameters' , 'gga-dynamic-placeholder-images' ) ?></strong><br/>
		<strong>columns</strong> - <?php _e( 'Number of columns for the grid, defaults to 3', 'gga-dynamic-placeholder-images' ); ?><br/>
		<strong>width</strong> - <?php _e( 'Image width, defaults to 300', 'gga-dynamic-placeholder-images' ); ?><br/>
		<strong>height</strong> - <?php _e( 'Image height, defaults to 300', 'gga-dynamic-placeholder-images' ); ?><br/>
		<strong>class</strong> - <?php _e( 'CSS class for the grid, defaults to gga-dynamic-images-attribution', 'gga-dynamic-placeholder-images' ); ?><br/>
		<br/>
		<strong><?php _e( 'Example', 'gga-dynamic-placeholder-images' ) ?>:</strong> [dynamic-images-attribution columns=4 width=200 height=200 class='my-image-attributions']
	</p>


	<h3 class="title" id="gga-help-cache"><?php _e( 'Cache', 'gga-dynamic-placeholder-images' ); ?></h3>
	<p>
		<?php _e( 'The plugin will generate resized images to the <strong>gga-dynamic-placeholder-images</strong> folder in the uploads folder.', 'gga-dynamic-placeholder-images' ) ?>
		<?php _e( 'For better performance, future requests for a specific image size will use the cached resized image rather than having to generate a new one each time.', 'gga-dynamic-placeholder-images' ) ?>
		<?php printf( __( 'These cached images can be purged on the <a href="%1s">Cache</a> tab to clear up disk space.', 'gga-dynamic-placeholder-images' ), admin_url( 'options-general.php?page=gga-dynamic-images-settings&tab=gga-dynamic-images-settings-cache' ) ) ?>
		<?php _e( 'The plugin will also associate a requested image size to a randomly chosen image.', 'gga-dynamic-placeholder-images' ) ?>
		<?php _e( 'Future requests for the same image size will return the same image.', 'gga-dynamic-placeholder-images' ) ?>
		<?php _e( 'These associations can also be purged on the Cache tab.', 'gga-dynamic-placeholder-images' ) ?>
		<br/><br/>
		<?php _e( 'Future versions of this plugin will be able to clean up the cache automatically.', 'gga-dynamic-placeholder-images' ) ?>
	</p>



	<h3 class="title"><?php _e( 'Filters', 'gga-dynamic-placeholder-images' ); ?></h3>
		<ul>
			<li>
				<strong>gga-dynamic-images-image-url</strong>: <?php _e( 'Generates a URL to a dynamic image', 'gga-dynamic-placeholder-images' ); ?>
				<br/>
				<strong><?php _e( 'Parameters', 'gga-dynamic-placeholder-images' ); ?>:</strong>
				<ul>
					<li class="indent">$url - <?php _e( 'leave blank', 'gga-dynamic-placeholder-images' ); ?></li>
					<li class="indent">$width - <?php _e( 'desired image width', 'gga-dynamic-placeholder-images' ); ?></li>
					<li class="indent">$height - <?php _e( 'desired image height', 'gga-dynamic-placeholder-images' ); ?></li>
					<li class="indent">$tag - <?php _e( 'optional, specific image tag', 'gga-dynamic-placeholder-images' ); ?></li>
				</ul>
				<?php _e( 'Example: ', 'gga-dynamic-placeholder-images'); ?>$url = apply_filters( 'gga-dynamic-images-image-url', '', 300, 400 );
			</li>

			<li>
				<strong>gga-dynamic-images-attribution-shortcode-html</strong>: <?php _e( 'Allows you to override the image attribution shortcode HTML', 'gga-dynamic-placeholder-images' ); ?>
				<br/>
				<strong><?php _e( 'Parameters', 'gga-dynamic-placeholder-images' ); ?>:</strong>
				<ul>
					<li class="indent">$html - <?php _e( 'HTML generated by the shortcode', 'gga-dynamic-placeholder-images' ); ?></li>
					<li class="indent">$posts - <?php _e( 'list of images tagged as dynamic images', 'gga-dynamic-placeholder-images' ); ?></li>
				</ul>
				<?php _e( 'Example: ', 'gga-dynamic-placeholder-images'); ?><br/>
				add_filter( 'gga-dynamic-images-attribution-shortcode-html', 'my_attribution_html', 10, 2 );<br/>
				function my_attribution_html( $html, $posts ) { <br/>
					// generate your own custom HTML here<br/>
					return $html;
				}<br/>

			</li>

		</ul>
	<p>
	</p>

	<h3 class="title"><?php _e( 'Contact', 'gga-dynamic-placeholder-images' ); ?></h3>
	<p>
		<?php _e( 'E-Mail', 'gga-dynamic-placeholder-images' ) ?>: <a href="mailto:pete@petenelson.com">pete@petenelson.com</a><br/>
		<?php _e( 'Twitter', 'gga-dynamic-placeholder-images' ) ?>: <a href="https://twitter.com/GunGeekATX" target="_blank">@GunGeekATX</a><br/>
		<?php _e( 'GitHub', 'gga-dynamic-placeholder-images' ) ?>: <a href="https://github.com/petenelson/gga-dynamic-placeholder-images" target="_blank">https://github.com/petenelson/gga-dynamic-placeholder-images</a><br/>
	</p>

</div>
