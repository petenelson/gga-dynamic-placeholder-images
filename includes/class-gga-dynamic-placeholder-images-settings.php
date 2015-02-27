<?php

if (!defined( 'ABSPATH' )) exit('restricted access');

if (!class_exists('GGA_Dynamic_Placeholder_Images_Settings')) {

	class GGA_Dynamic_Placeholder_Images_Settings {

		private $plugin_name 			= 'gga-dynamic-images';
		private $settings_page 			= 'gga-dynamic-images-settings';
		private $settings_key_general 	= 'gga-dynamic-images-settings-general';
		private $settings_key_api 		= 'gga-dynamic-images-settings-api';
		private $settings_key_help 		= 'gga-dynamic-images-settings-help';
		private $plugin_settings_tabs 	= array();


		public function plugins_loaded() {
			// admin menus
			if ( is_admin() ) {
				add_action( 'admin_init', array( $this, 'admin_init' ) );
				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
				add_action( 'admin_notices', array( $this, 'activation_admin_notice' ) );
			}

			add_filter( $this->plugin_name . '-setting-is-enabled', array( $this, 'setting_is_enabled' ), 10, 3 );
			add_filter( $this->plugin_name . '-setting-get', array( $this, 'setting_get' ), 10, 3 );

		}


		function get_option_filter( $value, $key ) {
			$options = get_option( $this->options );
			if ( ! empty( $options ) && isset( $options[ $key ] ) ) {
				return $options[ $key ];
			}
			else {
				return $value;
			}
		}



		public function activation_hook() {

			// create default settings
			add_option( $this->settings_key_general, array(
				'name' => 'Dynamic Images',
			), '', $autoload = 'no' );

			add_option( $this->settings_key_api, array(
				'api-enabled' => '0',
				'api-endpoint' => 'images-api',
			), '', $autoload = 'no' );



			add_option( $this->settings_key_filler, array('custom-words' => $custom, 'filler-words' => $filler), '', $autoload = 'no' );

			// add an option so we can show the activated admin notice
			add_option( $this->plugin_name . '-plugin-activated', '1' );

		}


		function activation_admin_notice() {
			if ( '1' === get_option( $this->plugin_name . '-plugin-activated' ) ) {
				?>
					<div class="updated">
						<p><?php
							echo sprintf( __( '<strong>Dynamic Placeholder Images activated!</strong> Please visit the <a href="%s">Settings</a> page to customize your image generator.', 'gga-dynamic-placeholder-images' ), admin_url( 'options-general.php?page=' . $this->plugin_name . '-settings' ) );
						?></p>
					</div>
				<?php
				delete_option( $this->plugin_name . '-plugin-activated' );
			}
		}


		public function deactivation_hook() {
			// placeholder in case we need deactivation code
		}


		function admin_init() {
			$this->register_general_settings();
			$this->register_api_settings();
			$this->register_help_tab();
		}


		function register_general_settings() {
			$key = $this->settings_key_general;
			$this->plugin_settings_tabs[$key] = __('General', 'gga-dynamic-placeholder-images');

			register_setting( $key, $key, array( $this, 'general_settings_sanitize' ) );

			$section = 'general';

			add_settings_section( $section, '', array( $this, 'section_header' ), $key );

			$permalink_structure = get_option( 'permalink_structure' );
			$permalink_warning = empty($permalink_structure) ? ' (please anable any non-default Permalink structure)' : '';

			add_settings_field( 'name', __('Your Dynamic Placholder Name', 'gga-dynamic-placeholder-images'), array( $this, 'settings_input' ), $key, $section,
				array('key' => $key, 'name' => 'name', 'size' => 20, 'maxlength' => 50, 'after' => 'Example: Bacon Mockup, Place Kitten'));


			$after = 'Example: dynamic-images (ex: /dynamic-images/200/200) or leave blank to operate at the root of your site.' . $permalink_warning;

			add_settings_field( 'base-url', __('Base URL', 'gga-dynamic-placeholder-images'), array( $this, 'settings_input' ), $key, $section,
				array('key' => $key, 'name' => 'base-url', 'size' => 20, 'maxlength' => 50, 'after' => $after));

		}


		function general_settings_sanitize( $settings ) {

			if ( isset( $settings['base-url'] ) ) {
				$settings['base-url'] = sanitize_key( $settings['base-url'] );
			}

			return $settings;
		}


		function register_api_settings() {
			$key = $this->settings_key_api;
			$this->plugin_settings_tabs[$key] = __('API', 'gga-dynamic-placeholder-images');

			register_setting( $key, $key, array( $this, 'api_settings_sanitize') );

			$section = 'api';

			add_settings_section( $section, '', array( $this, 'section_header' ), $key );

			add_settings_field( 'api-enabled', __('Enabled', 'gga-dynamic-placeholder-images'), array( $this, 'settings_yes_no' ), $key, $section,
				array('key' => $key, 'name' => 'api-enabled'));

			$permalink_structure = get_option( 'permalink_structure' );
			$permalink_warning = empty($permalink_structure) ? ' (please anable any non-default Permalink structure)' : '';

			add_settings_field( 'api-endpoint', __('Endpoint Page Name', 'gga-dynamic-placeholder-images'), array( $this, 'settings_input' ), $key, $section,
				array('key' => $key, 'name' => 'api-endpoint', 'size' => 20, 'maxlength' => 50, 'after' => 'Example: images-api, bacon-images-api, etc' . $permalink_warning));

		}


		function api_settings_sanitize( $settings ) {

			if ( isset( $settings['api-endpoint'] ) ) {
				$settings['api-endpoint'] = sanitize_key( $settings['api-endpoint'] );
			}

			return $settings;
		}



		function register_help_tab() {
			$key = $this->settings_key_help;
			$this->plugin_settings_tabs[$key] =  __('Help', 'gga-dynamic-placeholder-images');

			register_setting( $key, $key );

			$section = 'help';

			add_settings_section( $section, '', array( $this, 'section_header' ), $key );

		}


		function setting_is_enabled($enabled, $key, $setting) {
			return '1' === $this->setting_get('0', $key, $setting);
		}


		function setting_get($value, $key, $setting) {

			$args = wp_parse_args( get_option($key),
				array(
					$setting => $value,
				)
			);

			return $args[$setting];
		}


		function settings_input($args) {

			$args = wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'maxlength' => 50,
					'size' => 30,
					'after' => '',
				)
			);


			$name = $args['name'];
			$key = $args['key'];
			$size = $args['size'];
			$maxlength = $args['maxlength'];

			$option = get_option($key);
			$value = isset($option[$name]) ? esc_attr($option[$name]) : '';

			echo "<div><input id='{$name}' name='{$key}[{$name}]'  type='text' value='" . $value . "' size='{$size}' maxlength='{$maxlength}' /></div>";
			if (!empty($args['after']))
				echo '<div>' . __($args['after'], 'gga-dynamic-placeholder-images') . '</div>';

		}


		function settings_textarea($args) {

			$args = wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'rows' => 10,
					'cols' => 40,
					'after' => '',
				)
			);


			$name = $args['name'];
			$key = $args['key'];
			$rows = $args['rows'];
			$cols = $args['cols'];

			$option = get_option($key);
			$value = isset($option[$name]) ? esc_attr($option[$name]) : '';

			echo "<div><textarea id='{$name}' name='{$key}[{$name}]' rows='{$rows}' cols='{$cols}'>" . $value . "</textarea></div>";
			if (!empty($args['after']))
				echo '<div>' . $args['after'] . '</div>';

		}


		function settings_yes_no($args) {

			$args = wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'after' => '',
				)
			);

			$name = $args['name'];
			$key = $args['key'];

			$option = get_option($key);
			$value = isset($option[$name]) ? esc_attr($option[$name]) : '';

			if (empty($value))
				$value = '0';

			echo '<div>';
			echo "<label><input id='{$name}_1' name='{$key}[{$name}]'  type='radio' value='1' " . ('1' === $value ? " checked=\"checked\"" : "") . "/>" . __('Yes', 'gga-dynamic-placeholder-images') . "</label> ";
			echo "<label><input id='{$name}_0' name='{$key}[{$name}]'  type='radio' value='0' " . ('0' === $value ? " checked=\"checked\"" : "") . "/>" . __('No', 'gga-dynamic-placeholder-images') . "</label> ";
			echo '</div>';

			if (!empty($args['after']))
				echo '<div>' . __($args['after'], 'gga-dynamic-placeholder-images') . '</div>';
		}


		function admin_menu() {
			add_options_page( __('Dynamic Placeholder Images', 'gga-dynamic-placeholder-images'), __('Dynamic Placeholder Images', 'gga-dynamic-placeholder-images'), 'manage_options', $this->settings_page, array($this, 'options_page' ), 30);
		}


		function options_page() {

			$tab = !empty( $_GET['tab'] ) ? $_GET['tab'] : $this->settings_key_general;
			?>
			<div class="wrap">
				<?php $this->plugin_options_tabs(); ?>
				<form method="post" action="options.php" class="options-form">
					<?php settings_fields( $tab ); ?>
					<?php do_settings_sections( $tab ); ?>
					<?php
						if ($this->settings_key_help !== $tab)
							submit_button(__('Save Settings', 'gga-dynamic-placeholder-images'), 'primary', 'submit', true);
					?>
				</form>
			</div>
			<?php

			if ( ! empty( $_GET[ 'settings-updated'] ) ) {
				flush_rewrite_rules( );
			}

		}


		function plugin_options_tabs() {
			$current_tab = !empty( $_GET['tab'] ) ? $_GET['tab'] : $this->settings_key_general;
			echo '<h2>' . __('Dynamic Placeholder Images Settings', 'gga-dynamic-placeholder-images') . '</h2><h2 class="nav-tab-wrapper">';
			foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
				$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
				echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->settings_page . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
			}
			echo '</h2>';
		}


		function section_header($args) {

			switch ($args['id']) {
				case 'help';
					include_once 'admin-help.php';
					break;
				case 'api':
					$output = __('Allows for a JSON API to your image generator.', 'gga-dynamic-placeholder-images');
					$endpoint = $this->setting_get( '', $this->settings_key_api, 'api-endpoint' );
					$enabled = $this->setting_is_enabled( '', $this->settings_key_api, 'api-enabled' );
					if ( ! empty( $enabled ) ) {
						$output .= '<br/>' . __('Example', 'gga-dynamic-placeholder-images') . ': ';
						$url = home_url( $endpoint ) . '/image-tags/';
						$output .= '<a target="_blank" href="' . $url . '">' . $url . '</a>';
					}
					break;
			}

			if (!empty($output))
				echo '<p class="settings-section-header">' . $output . '</p>';

		}


	}

}