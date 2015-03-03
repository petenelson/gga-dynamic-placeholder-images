<?php

					$output = __('Allows for a JSON API to your image generator.', 'gga-dynamic-placeholder-images');
					$endpoint = $this->setting_get( '', $this->settings_key_api, 'api-endpoint' );
					$enabled = $this->setting_is_enabled( '', $this->settings_key_api, 'api-enabled' );
					if ( ! empty( $enabled ) ) {
						$output .= '<br/>' . __('Example', 'gga-dynamic-placeholder-images') . ': ';
						$url = home_url( $endpoint ) . '/image-tags/';
						$output .= '<a target="_blank" href="' . $url . '">' . $url . '</a>';
					}
