<?php
global $gga_image_post;
global $gga_attrib_args;

$attrib_to = get_post_meta( $gga_image_post->ID, $this->meta_prefix . 'attribute_to', true );
$attrib_url = get_post_meta( $gga_image_post->ID, $this->meta_prefix . 'attribute_url', true );

$cc_url = '';

if ( 'on' === get_post_meta( $gga_image_post->ID, $this->meta_prefix . 'cc_by', true ) );
	$cc_url = 'http://creativecommons.org/licenses/by/2.0/';

if ( 'on' === get_post_meta( $gga_image_post->ID, $this->meta_prefix . 'cc_sa', true ) );
	$cc_url = 'http://creativecommons.org/licenses/by-sa/2.0/';

$image_url = apply_filters( $this->plugin_name . '-image-url', '', $gga_attrib_args['width'], $gga_attrib_args['height'], $gga_image_post->post_name );
?>
<div class="attribImage">
	<div class="attribImage-inner">
		<a class="image-link" href="<?php echo $image_url; ?>"><img class="image-thumbnail" src="<?php echo $image_url; ?>" alt="<?php echo esc_attr( $gga_image_post->post_name ); ?>" width="<?php echo $gga_attrib_args['width']; ?>" height="<?php echo $gga_attrib_args['height']; ?>" /></a>
		<div class="image-meta">
			<div class="image-tag">tag: <?php echo esc_html( $gga_image_post->post_name ); ?></div>
			<?php if ( $attrib_to !== false && !empty( $attrib_to ) ) { ?>
				<div class="attribute-to">by <a href="<?php echo $attrib_url ?>" target="_blank"><?php echo esc_html( $attrib_to ); ?></a></div>
			<?php } ?>
			<?php if ( 'on' === get_post_meta( $gga_image_post->ID, $this->meta_prefix . 'cc_by', true ) ) { ?>
				<span class="cc cc-by" title="<?php _e( 'Creative Commons Attribution', 'gga-dynamic-placeholder-images' ); ?>"><?php echo $this->cc_img_html( '', 'cc_by' ); ?></span>
			<?php } ?>
			<?php if ( 'on' === get_post_meta( $gga_image_post->ID, $this->meta_prefix . 'cc_sa', true ) ) { ?>
				<span class="cc cc-sa" title="<?php _e( 'Creative Commons Share Alike', 'gga-dynamic-placeholder-images' ); ?>"><?php echo $this->cc_img_html( '', 'cc_sa' ); ?></span>
			<?php } ?>
			<?php if ( 'on' === get_post_meta( $gga_image_post->ID, $this->meta_prefix . 'cc_nc', true ) ) { ?>
				<span class="cc cc-nc" title="<?php _e( 'Creative Commons Non-Commercial', 'gga-dynamic-placeholder-images' ); ?>"><?php echo $this->cc_img_html( '', 'cc_nc' ); ?></span>
			<?php } ?>
			<?php if ( $cc_url != '' ) { ?>
				<a class="some-rights-reserved" href="<?php echo $cc_url ?>" target="_blank"><?php _e( 'Some rights reserved', 'gga-dynamic-placeholder-images' ); ?></a>
			<?php } ?>
		</div>
	</div>
</div><!-- .attribImage -->
<?php
