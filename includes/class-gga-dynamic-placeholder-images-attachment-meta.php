<?php
if ( ! defined( 'ABSPATH' ) ) wp_die( 'restricted access' );

if ( ! class_exists( 'GGA_Dynamic_Placeholder_Images_Attachment_Meta' ) ) {

	class GGA_Dynamic_Placeholder_Images_Attachment_Meta {

		var $plugin_base_url = '';

		public function plugins_loaded() {
			add_filter( 'attachment_fields_to_edit', array( $this, 'attachment_fields_to_edit' ), 10, 2 );
			add_filter( "attachment_fields_to_save", array( $this, 'attachment_fields_to_save' ), 10 , 2 );
		}


		function attachment_fields_to_edit( $form_fields, $post ) {

			$form_fields['gga_image_is_mockup_image'] =array(
				'label' => __( 'Is Dynamic Placeholder Image?', 'gga-dynamic-placeholder-images' ),
				'input' => 'html',
				'html' => $this->checkbox( $post, 'gga_image_is_mockup_image' ),
				'value' => 'on',
			);

			$form_fields['gga_image_attribute_to'] =array(
				'label' => __( 'Attribute To', 'gga-dynamic-placeholder-images' ) . ':',
				'value' =>  get_post_meta( $post->ID, '_gga_image_attribute_to', true ),
			);

			$form_fields['gga_image_attribute_url'] =array(
				'label' => __( 'Attribute Url', 'gga-dynamic-placeholder-images' ) . ':',
				'value' => get_post_meta( $post->ID, '_gga_image_attribute_url', true ),
			);

			$form_fields['gga_image_cc_by'] =array(
				'label' => __( 'Creative Commons Attribution', 'gga-dynamic-placeholder-images' ) . ':',
				'input' => 'html',
				'html' => $this->checkbox( $post, 'gga_image_cc_by', array( 'append_html' => apply_filters( 'gga-dynamic-images-cc-img-html', '', 'cc_by' ) ) ),
				'value' => 'on',
			);

			$form_fields['gga_image_cc_sa'] =array(
				'label' => __( 'Creative Commons Share Alike', 'gga-dynamic-placeholder-images' ) . ':',
				'input' => 'html',
				'html' => $this->checkbox( $post, 'gga_image_cc_sa', array( 'append_html' => apply_filters( 'gga-dynamic-images-cc-img-html', '', 'cc_sa' ) ) ),
				'value' => 'on',
			);

			$form_fields['gga_image_cc_nc'] =array(
				'label' => __( 'Creative Commons Non-Commercial', 'gga-dynamic-placeholder-images' ) . ':',
				'input' => 'html',
				'html' => $this->checkbox( $post, 'gga_image_cc_nc', array( 'append_html' => apply_filters( 'gga-dynamic-images-cc-img-html', '', 'cc_nc' ) ) ),
				'value' => 'on',
			);


			return $form_fields;
		}


		function attachment_fields_to_save( $post, $attachment ) {

			$textfields = array( 'gga_image_attribute_to', 'gga_image_attribute_url' );
			foreach ( $textfields as $field ) {
				if ( isset( $attachment[ $field ] ) ) {
					update_post_meta( $post['ID'], '_' . $field, sanitize_text_field( $attachment[ $field ] ) );
				}
			}

			$checkboxes = array( 'gga_image_is_mockup_image', 'gga_image_cc_by', 'gga_image_cc_sa', 'gga_image_cc_nc' );
			foreach ( $checkboxes as $field ) {
				if ( !empty ( $attachment[ $field ] ) && $attachment[ $field ] === 'on' ) {
					update_post_meta( $post['ID'], '_' . $field, 'on' );
				} else {
					update_post_meta( $post['ID'], '_' . $field, '' );
				}
			}

			return $post;

		}


		private function checkbox( $post, $id, $args = null ) {
			$meta_on = get_post_meta( $post->ID, '_' . $id, true ) === 'on';
			$checked = $meta_on ? 'checked="checked"' : '';
			$args = wp_parse_args( $args, array( 'append_html' => '' ) );
			return "<input type='checkbox' name='attachments[{$post->ID}][$id]' id='attachments[{$post->ID}][$id]' value='on' {$checked} />" . $args['append_html'];
		}


	}

}
