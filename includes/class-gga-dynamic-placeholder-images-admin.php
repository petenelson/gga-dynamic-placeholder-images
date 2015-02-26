<?php

if ( !defined( 'ABSPATH' ) ) exit( 'restricted access' );

if ( !class_exists( 'GGA_Dynamic_Placeholder_Images_Admin' ) ) {

	class GGA_Dynamic_Placeholder_Images_Admin {

		var $plugin_base_url = '';

		public function plugins_loaded() {

			add_filter( 'attachment_fields_to_edit', array( $this, 'attachment_fields_to_edit' ), 10, 2 );
			add_filter( "attachment_fields_to_save", array( $this, 'attachment_fields_to_save' ), 10 , 2 );
			//add_action( 'add_meta_boxes_attachment', array( $this, 'add_meta_box_attachment' ) );

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


		function checkbox( $post, $id, $args = null ) {
			$meta_on = get_post_meta( $post->ID, '_' . $id, true ) === 'on';
			$checked = $meta_on ? 'checked="checked"' : '';

			$args = wp_parse_args( $args, array( 'append_html' => '' ) );

			return "<input type='checkbox' name='attachments[{$post->ID}][$id]' id='attachments[{$post->ID}][$id]' value='on' {$checked} />" . $args['append_html'];
		}


		function add_meta_box_attachment( ) {

			//var_dump(get_current_screen());

			add_meta_box( 'gga-dynamic-image', 'GGA Dynamic Image', array( $this, 'render_metabox' ), 'attachment', 'normal', 'low', $callback_args = null );


		}


		function render_metabox( $args ) {


			// TODO metabox nonce
?>

			<style>

				.gga-metabox td, .gga-metabox th {
					border-bottom: 1px solid #e9e9e9;
				}

			</style>

			<div class="inside">

				<table class="form-table gga-metabox">
					<tbody>
						<tr>
							<th style="width:18%">
								<label for="_gga_image_is_mockup_image">Is Bacon Mockup Image:</label>
							</th>
							<td>
								<input type="checkbox" name="_gga_image_is_mockup_image" id="_gga_image_is_mockup_image" checked="checked">
								<span class="cmb_metabox_description"></span>
							</td>
						</tr>

						<tr><th style="width:18%"><label for="_gga_image_attribute_to">Attribute To:</label></th><td><input type="text" name="_gga_image_attribute_to" id="_gga_image_attribute_to" value="cookbookman17"><p class="cmb_metabox_description"></p></td></tr><tr><th style="width:18%"><label for="_gga_image_attribute_url">Attribute Url:</label></th><td><input type="text" name="_gga_image_attribute_url" id="_gga_image_attribute_url" value="http://www.flickr.com/photos/cookbookman/"><p class="cmb_metabox_description"></p></td></tr><tr><th style="width:18%"><label for="_gga_image_cc_by">CC Attribute:</label></th><td><input type="checkbox" name="_gga_image_cc_by" id="_gga_image_cc_by" checked="checked"><span class="cmb_metabox_description"></span></td></tr><tr><th style="width:18%"><label for="_gga_image_cc_nc">CC Non-Commercial:</label></th><td><input type="checkbox" name="_gga_image_cc_nc" id="_gga_image_cc_nc"><span class="cmb_metabox_description"></span></td></tr><tr><th style="width:18%"><label for="_gga_image_cc_sa">CC Share Alike:</label></th><td><input type="checkbox" name="_gga_image_cc_sa" id="_gga_image_cc_sa"><span class="cmb_metabox_description"></span></td></tr></tbody></table>

			</div>
			<?php
		}




	}

}
