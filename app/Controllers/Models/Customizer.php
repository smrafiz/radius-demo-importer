<?php
/**
 * Customizer Import Model Class.
 *
 * Code is mostly from the Customizer Export/Import plugin.
 *
 * @see https://wordpress.org/plugins/customizer-export-import/
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Controllers\Models;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Content Import Class.
 */
class Customizer {
	/**
	 * Imports uploaded mods
	 *
	 * @param string $customizerFile Customizer file.
	 * @param string $excludeImages Exclude Images.
	 *
	 * @return void
	 */
	public function import( $customizerFile, $excludeImages ) {
		global $wp_customize;

		$template      = get_template();
		$data          = maybe_unserialize( file_get_contents( $customizerFile ) );
		$excludeImages = 'true' === $excludeImages;

		// Data checks.
		if ( 'array' != gettype( $data ) ) {
			$error = __( 'Error importing settings! Please check that you uploaded a customizer export file.', 'radius-demo-importer' );

			return;
		}

		if ( ! isset( $data['template'] ) || ! isset( $data['mods'] ) ) {
			$error = __( 'Error importing settings! Please check that you uploaded a customizer export file.', 'radius-demo-importer' );

			return;
		}

		if ( $data['template'] != $template ) {
			$error = __( 'Error importing settings! The settings you uploaded are not for the current theme.', 'radius-demo-importer' );

			return;
		}

		// Import Images.
		if ( ! $excludeImages ) {
			$data['mods'] = $this->importImages( $data['mods'] );
		}

		// Import custom options.
		if ( isset( $data['options'] ) ) {

			// Load WordPress Customize Setting Class.
			if ( ! class_exists( 'WP_Customize_Setting' ) ) {
				require_once ABSPATH . WPINC . '/class-wp-customize-setting.php';
			}

			foreach ( $data['options'] as $optionKey => $optionValue ) {
				$option = new CustomizerOption(
					$wp_customize,
					$optionKey,
					[
						'default'    => '',
						'type'       => 'option',
						'capability' => 'edit_theme_options',
					]
				);

				$option->import( $optionValue );
			}
		}

		// If wp_css is set then import it.
		if ( function_exists( 'wp_update_custom_css_post' ) && isset( $data['wp_css'] ) && '' !== $data['wp_css'] ) {
			wp_update_custom_css_post( $data['wp_css'] );
		}

		// Loop through theme mods and update them.
		if ( ! empty( $data['mods'] ) ) {
			foreach ( $data['mods'] as $key => $value ) {
				set_theme_mod( $key, $value );
			}
		}
	}

	/**
	 * Imports images for settings saved as mods.
	 *
	 * @param array $mods An array of customizer mods.
	 *
	 * @return array
	 */
	private function importImages( $mods ) {
		foreach ( $mods as $key => $value ) {

			// For repeater fields.
			if ( $this->isJSON( $value ) ) {
				$dataArray = json_decode( $value );

				foreach ( $dataArray as $dataKey => $dataObject ) {
					foreach ( $dataObject as $subDataKey => $subDataValue ) {
						if ( $this->isImageUrl( $subDataValue ) ) {
							$subData = $this->mediaHandleSideload( $subDataValue );

							if ( ! is_wp_error( $subData ) ) {
								$dataObject->$subDataKey = $subData->url;
							}
						} else {
							$dataObject->$subDataKey = $subDataValue;
						}
					}

					$dataArray[ $dataKey ] = $dataObject;
				}

				$mods[ $key ] = json_encode( $dataArray );
			} elseif ( $this->isImageUrl( $value ) ) {
				$data = $this->mediaHandleSideload( $value );

				if ( ! is_wp_error( $data ) ) {
					$mods[ $key ] = $data->url;

					// Handle header image controls.
					if ( isset( $mods[ $key . '_data' ] ) ) {
						$mods[ $key . '_data' ] = $data;
						update_post_meta( $data->attachment_id, '_wp_attachment_is_custom_header', get_stylesheet() );
					}
				}
			}
		}

		return $mods;
	}

	/**
	 * Taken from the core media_sideload_image function and
	 * modified to return an array of data instead of html.
	 *
	 * @param string $file The image file path.
	 *
	 * @return bool|int|\stdClass|string|\WP_Error
	 */
	private function mediaHandleSideload( $file ) {
		$data = new \stdClass();

		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		if ( ! empty( $file ) ) {
			// Set variables for storage, fix file filename for query strings.
			preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png|svg)\b/i', $file, $matches );

			$fileArray         = [];
			$fileArray['name'] = basename( $matches[0] );

			// Download file to temp location.
			$fileArray['tmp_name'] = download_url( $file );

			// If error storing temporarily, return the error.
			if ( is_wp_error( $fileArray['tmp_name'] ) ) {
				return $fileArray['tmp_name'];
			}

			// Do the validation and storage stuff.
			$id = media_handle_sideload( $fileArray, 0 );

			// If error storing permanently, unlink.
			if ( is_wp_error( $id ) ) {
				@unlink( $fileArray['tmp_name'] );

				return $id;
			}

			// Build the object to return.
			$meta                = wp_get_attachment_metadata( $id );
			$data->attachment_id = $id;
			$data->url           = wp_get_attachment_url( $id );
			$data->thumbnail_url = wp_get_attachment_thumb_url( $id );
			$data->height        = $meta['height'];
			$data->width         = $meta['width'];
		}

		return $data;
	}

	/**
	 * Checks to see whether a url is an image url or not.
	 *
	 * @param string $url The url to check.
	 *
	 * @return bool
	 */
	private function isImageUrl( $url ) {
		if ( is_string( $url ) && preg_match( '/\.(jpg|jpeg|png|gif|svg)/i', $url ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the input is JSON.
	 *
	 * @param string $string String to check.
	 *
	 * @return bool
	 */
	private function isJSON( $string ) {
		return is_string( $string ) && is_array( json_decode( $string, true ) );
	}
}
