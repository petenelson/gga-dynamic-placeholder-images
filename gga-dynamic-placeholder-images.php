<?php 
/*
 * Plugin Name: GGA Dynamic Placeholder Images
 * Plugin URI: https://github.com/petenelson/gga-dynamic-placeholder-images
 * Description: Plugin for managing and serving up placeholder images (such as <a href="http://baconmockup.com/200/200" target="_blank">http://baconmockup.com/200/200</a>)
 * Version: 1.1
 * Author: Pete Nelson (@GunGeekATX)
 * Author URI: https://twitter.com/GunGeekATX
 * 
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU 
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume 
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without 
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 */


class GGA_Dynamic_Placeholder_Images {

	var $sizes;
	var $meta_prefix = '_gga_image_';
	var $title = 'GGA Dynamic Image';  // default
	var $options = '_gga_image_options_';
	var $add_expires = true;


	function __construct() {

		add_action( 'init', array(&$this, 'init'));
		add_filter( 'cmb_meta_boxes', array(&$this, 'define_cmb_metaboxes' ));
		add_shortcode( 'gga-image-attribution', array(&$this, 'image_attribution') );

		if (is_admin())	{
			add_action( 'admin_menu', array(&$this, 'admin_menu' ));
			add_action( 'admin_init', array(&$this, 'admin_register_settings') );
		}


		// allows us to set the title of our placeholder images
		$opt = get_option( $this->options );
		if (false !== $opt && isset($opt['title']))
			$this->title = $opt['title'];

	}


	function init() {

		// get it here: https://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress
		if (!class_exists('cmb_Meta_Box'))
			require_once(plugin_dir_path( __FILE__ ) . 'custom-metaboxes/init.php');


		$width = $this->getvar_as_int('gga-image-width');
		$height = $this->getvar_as_int('gga-image-height');
		$slug = $this->getvar('gga-image-slug');

		// for .htaccess
		// RewriteEngine On
		// RewriteRule ^(\d*)/(\d*)/([0-9a-zA-Z\-]*)$ index.php?gga-image=1&gga-image-width=$1&gga-image-height=$2&gga-image-slug=$3  [NC]
		// RewriteRule ^(\d*)/(\d*)/$ index.php?gga-image=1&gga-image-width=$1&gga-image-height=$2  [NC]
		// RewriteRule ^(\d*)/(\d*)$ index.php?gga-image=1&gga-image-width=$1&gga-image-height=$2  [NC,L]

		// translates into this		
		// http://localhost:8080/wp-baconmockup/index.php?gga-image=1&gga-image-width=300&gga-image-height=300&gga-image-slug=bacon


		if ($this->getvar('gga-image') == '1' && $width !== 0 && $height !== 0) {

			$this->load_image_sizes();
			$id = 0;

			if ($slug === 'random') {
				$id = $this->get_random_image_id();
				$this->add_expires = false;
			}
			else if (!empty($slug)) {
				$id = $this->get_image_id_by_slug($slug); 
				if ($id === 0) {
					$this->show_404_and_die();
				}
			} else {
				$id = $this->get_existing_image_id_by_dimensions($width, $height);
			}

			if ($id === 0 || $id === false) {
				$id = $this->get_random_image_id();
			}

			if ($id === 0 || $id === false || $id === NULL) { 
					$this->show_404_and_die();
			}
			else {
				$this->generate_image($id, $width, $height);
				$this->stream_image($id, $width, $height);
			}

			die();
		}


	}

	function admin_menu() {
		add_options_page( 'GGA Dynamic Image Options', 'GGA Dynamic Image', 'manage_options', 'gga-dynamic-image-options', array(&$this, 'admin_options_page') );
	}

	function admin_options_page() {
		$opt = $this->options;

		if ( !current_user_can( 'manage_options' ) )  
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );


		?>
		
		<div class="wrap">
			<h2>GGA Dynamic Image Options</h2>
			<form method="post" action="options.php" id="gga-dynamic-image-settings"> 
				<?php 
					settings_fields( $opt ); 
					do_settings_sections( $opt ); 
					submit_button(); 
				?>
			</form>

		</div>

		<?php
	}


	function admin_register_settings() {
		$opt = $this->options;

		register_setting( $opt, $opt);

		$section = $opt . '_general';

		add_settings_section($section, 'General', array(&$this, 'dynaminc_image_settings_section'), $opt);
		add_settings_field('title', 'Title:', array(&$this, 'dynaminc_image_title'), $opt, $section);

	}

	function dynaminc_image_settings_section() {}

	function dynaminc_image_title() {	
		$this->setting_input('title', 50, 50);
	}

	function setting_input($name, $size, $maxlength, $classes = '') {
		$options = get_option($this->options);
		$optionValue = isset($options[$name]) ? $options[$name] : "";

		echo "<input id='{$name}' name='" . $this->options . "[{$name}]' size='{$size}' maxlength='{$maxlength}' type='text' value='" . $optionValue . "' class=\"{$classes}\" />";
	}



	function show_404_and_die() {
		status_header(404);
		nocache_headers();
		include( get_404_template() );
		die();
	}

	function image_query_args() {

		return array( 
			'posts_per_page' => 1,
 			'post_type' => 'attachment',
 			'post_status' => 'inherit',
    		'post_mime_type' => 'image',
			'meta_key' => $this->meta_prefix . 'is_mockup_image',
			'meta_value' => 'on',
		);

	}

	function get_random_image_id() {
		$args = $this->image_query_args();
		$args['orderby'] = 'rand';
		return $this->get_image_id_from_query($args);
	}

	function get_image_id_by_slug($slug) {
		$args = $this->image_query_args();
		$args['name'] = $slug;
		return $this->get_image_id_from_query($args);
	}

	function get_image_id_from_query($args) {
		$id = 0;
		$query = new WP_Query($args);

		if ($query->have_posts()) 
			$id = $query->post->ID;

		return $id;
	}

	function stream_image($id, $w, $h) {

		$image_size_name = $this->image_size_name($w, $h);
		$image = get_post( $id );
		$fullsizepath = get_attached_file( $id );

		$dirname = pathinfo( $fullsizepath);

		$meta = wp_get_attachment_metadata( $id ); 
		$sizes = $meta['sizes'];

		if ($sizes[$image_size_name]) {

			$filename = path_join( $dirname['dirname'], $sizes[$image_size_name]['file'] );
			$filesize = filesize($filename);

			header('Content-Type: ' . $sizes[$image_size_name]['mime-type']);
			header('Content-Length: ' . $filesize);
			header('Content-Disposition: inline; filename=' . $image->post_name . '-' . $w . '-' . $h . '.jpg');  

			if ($this->add_expires) {
				$expires = 60*60*24*14;
				header('Pragma: public');
				header('Cache-Control: public');
				header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
				header('Last-Modified:Mon, 20 Aug 2012 19:20:21 GMT');
			}


			ob_clean();
			flush();
			readfile($filename);
			die();

		}
		else
			$this->show_404_and_die();

	}


	function save_images_sizes() {
		update_option( 'gga-dynamic-image-sizes', $this->sizes);
	}

	function load_image_sizes() {
		$this->sizes = get_option( 'gga-dynamic-image-sizes' );
		
		if ($this->sizes && is_array($this->sizes))  {
			foreach ($this->sizes as $s)
				add_image_size( $s->name, $s->w, $s->h, true );
		}
		else
			$this->sizes = array();

	}

	function add_image_size($w, $h) {
		$image_size_name = $this->image_size_name($w, $h);
		
		foreach ($this->sizes as $s) {
			if ($s->name === $image_size_name)
				return;
		}


		// size does not exist, add it
		$size = new stdClass();
		$size->name = $image_size_name;
		$size->w = $w;
		$size->h = $h;

		$this->sizes[] = $size;
		update_option( 'gga-dynamic-image-sizes',  $this->sizes);

		add_image_size( $image_size_name, $w, $h, true);

	}



	function generate_image($id, $w, $h) {


		$image_size_name = $this->image_size_name($w, $h);

		$this->add_image_size($w, $h);

		$exists = $this->image_size_exists($id, $w, $h);


		if (!$exists) {
			$image = get_post( $id );
			$fullsizepath = get_attached_file( $image->ID );
			include_once( ABSPATH . 'wp-admin/includes/image.php' );

			// don't call wp_generate_attachment_metadata because it regenerates existing images
			$metadata = wp_get_attachment_metadata( $id ); 

			$resized =  image_make_intermediate_size( $fullsizepath, $w, $h, $crop=true );

			$metadata['sizes'][$image_size_name] = $resized;

			wp_update_attachment_metadata( $id, $metadata );

			// save option so we know whoich image to use for the size
			if ($this->get_existing_image_id_by_dimensions($w, $h) === false)
				update_option( "_gga-image-for-{$w}-{$h}", $id);

		}


	}

	function get_existing_image_id_by_dimensions($w, $h) {
		return get_option("_gga-image-for-{$w}-{$h}", false);
	}



	function image_size_exists($id, $w, $h) {
		$meta = wp_get_attachment_metadata( $id );
		$sizes = $meta['sizes'];
		return isset($sizes[$this->image_size_name($w, $h)]);
	}


	function image_size_name($w, $h) {
		return 'gga-image-' . $w . '-' . $h;
	}


	function getvar_as_int($var) {
		if (!false == $var_value = $this->getvar($var))
			return intval($var_value);
		else
			return 0;
	}

	function getvar($var) {
		return isset($_GET[$var]) ? $_GET[$var] : false;
	}


	function define_cmb_metaboxes( array $meta_boxes) {
		

		$meta_boxes[] = array(
			'id'         => 'gga_image_meta_boxes',
			'title'      => $this->title,
			'pages'      => array( 'attachment' ), // Post type
			'context'    => 'normal',
			'priority'   => 'high',
			'show_names' => true, // Show field names on the left
			'fields'     => array(
				array(
					'name' => 'Is ' . $this->title . ' Image:',
					'id'   => $this->meta_prefix . 'is_mockup_image',
					'type' => 'checkbox',
				),
				array(
					'name' => 'Attribute To:',
					'id'   => $this->meta_prefix . 'attribute_to',
					'type' => 'text',
				),
				array(
					'name' => 'Attribute Url:',
					'id'   => $this->meta_prefix . 'attribute_url',
					'type' => 'text',
				),
				array(
					'name' => 'CC Attribute:',
					'id'   => $this->meta_prefix . 'cc_by',
					'type' => 'checkbox',
				),
				array(
					'name' => 'CC Non-Commercial:',
					'id'   => $this->meta_prefix . 'cc_nc',
					'type' => 'checkbox',
				),
				array(
					'name' => 'CC Share Alike:',
					'id'   => $this->meta_prefix . 'cc_sa',
					'type' => 'checkbox',
				),
			) 
		);

		return $meta_boxes;
	}


	function image_attribution() {

		$html = '<div id="attribImages">';

		$args = $this->image_query_args();
		$args['posts_per_page'] = -1;
		$args['orderby'] = 'name';
		$args['order'] = 'asc';

		$query = new WP_Query($args);

		global $post;
		while ($query->have_posts()) {
			$query->the_post();
			$id = get_the_id();


			$tag = $post->post_name;
			$image_url = site_url( $path = '/200/200/' . $tag);
			$cc_url = '';
			$attrib_to = get_post_meta( $id, $this->meta_prefix . 'attribute_to', true);
			$attrib_url = get_post_meta( $id, $this->meta_prefix . 'attribute_url', true);

			if ('on' === get_post_meta( $id, $this->meta_prefix . 'cc_by', true));
				$cc_url = 'http://creativecommons.org/licenses/by/2.0/';
			
			if ('on' === get_post_meta( $id, $this->meta_prefix . 'cc_sa', true));
				$cc_url = 'http://creativecommons.org/licenses/by-sa/2.0/';

			$html .= "
			<div class=\"attribImage\">
				<a class=\"meat\" href=\"{$image_url}\"><img class=\"meat\" src=\"{$image_url}\" alt=\"\" width=\"200\" height=\"200\" /></a>
				tag: {$tag}<br/>";

			if ('on' === get_post_meta( $id, $this->meta_prefix . 'cc_by', true))
				$html .= '<span class="cc cc-by" title="Attribution"></span>';

			if ('on' === get_post_meta( $id, $this->meta_prefix . 'cc_sa', true))
				$html .= '<span class="cc cc-sa" title="Share Alike"></span>';

			if ($cc_url != '')
				$html .= "<a href=\"{$cc_url}\" target=\"_blank\">Some rights reserved</a><br/>";

			if ($attrib_to !== false && !empty($attrib_to))
				$html .= "by <a href=\"{$attrib_url}\" target=\"_blank\">" . htmlspecialchars($attrib_to) . "</a>";

			$html .= '</div><!-- .attribImage -->';

		
		} 
					


		$html .= '<div class="clear"></div>
		</div><!-- #attribImages -->';
	

		wp_reset_postdata();

		return $html;

	}

	
}


new GGA_Dynamic_Placeholder_Images();

