<?php

if (!defined( 'ABSPATH' )) exit('restricted access');

if (!class_exists('GGA_Dynamic_Placeholder_Images_Admin')) {

	class GGA_Dynamic_Placeholder_Images_Admin {

		static $version = 'version';
		static $plugin_name = 'plugin-name';


		public function plugins_loaded() {

			add_filter( 'attachment_fields_to_edit', array( $this, 'attachment_fields_to_edit' ), null, 2 );

		}


		function attachment_fields_to_edit( $form_fields, $post ) {


    $field_value = get_post_meta( $post->ID, 'location', true );

    $form_fields['location'] = array(
        'value' => $field_value ? $field_value : '',
        'label' => __( 'Location' ),
        'helps' => __( 'Set a location for this attachment' ),
        'input' => 'html',
        'html' => '<input type="checkbox" />',
    );

			return $form_fields;

		}

	}

}
