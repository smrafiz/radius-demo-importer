<?php
/**
 * Settings Import Ajax Class.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Controllers\Ajax;

use RT\DemoImporter\Helpers\Fns;
use RT\DemoImporter\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Settings Import Ajax Class.
 */
class ImportSettings extends Ajax {
	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	public function register() {
		parent::register();

		add_action( 'wp_ajax_rtdi_import_settings', [ $this, 'import' ] );
	}

	/**
	 * Settings import process callback.
	 *
	 * @return void
	 */
	public function import() {
		Fns::verifyAjaxCall();

		$settings = $this->multiple ? Fns::keyExists( $this->config['demoData'][ $this->demoSlug ]['settingsJson'] ) : Fns::keyExists( $this->config['settingsJson'] );

		$settingsExists = isset( $settings ) && is_array( $settings );

		if ( $settingsExists ) {
			foreach ( $settings as $option ) {
				$optionFile = $this->demoUploadDir( $this->demoSlug ) . '/' . $option . '.json';
				$fileExists = file_exists( $optionFile );

				if ( $fileExists ) {
					$data = file_get_contents( $optionFile );

					if ( $data ) {
						update_option( $option, json_decode( $data, true ) );
					}
				}
			}
		}

		$forms = $this->multiple ? Fns::keyExists( $this->config['demoData'][ $this->demoSlug ]['fluentFormsJson'] ) : Fns::keyExists( $this->config['fluentFormsJson'] );

		$formsExists = isset( $forms ) || is_plugin_active( 'fluentform/fluentform.php' );

		$this->response(
			$formsExists ? 'rtdi_import_fluent_forms' : 'rtdi_import_widgets',
			$formsExists ? esc_html__( 'Importing Fluent forms', 'radius-demo-importer' ) : esc_html__( 'Importing widgets', 'radius-demo-importer' ),
			$settingsExists ? esc_html__( 'Theme settings imported', 'radius-demo-importer' ) : esc_html__( 'Settings import not needed', 'radius-demo-importer' )
		);
	}
}
