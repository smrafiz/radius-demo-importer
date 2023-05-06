<?php
/**
 * Customizer Import Ajax Class.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Controllers\Ajax;

use RT\DemoImporter\Helpers\Fns;
use RT\DemoImporter\Traits\SingletonTrait;
use RT\DemoImporter\Controllers\Models\Customizer;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Customizer Import Ajax Class.
 */
class CustomizerImport extends Ajax {
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

		add_action( 'wp_ajax_rtdi_import_customizer', [ $this, 'import' ] );
	}

	/**
	 * Customizer import process callback.
	 *
	 * @return void
	 */
	public function import() {
		Fns::verifyAjaxCall();

		$customizerFilePath = $this->demoUploadDir( $this->demoSlug ) . '/customizer.dat';

		$fileExists = file_exists( $customizerFilePath );

		if ( $fileExists ) {
			ob_start();

			// Import customizer data.
			( new Customizer() )->import( $customizerFilePath, $this->excludeImages );

			ob_end_clean();
		}

		// Response.
		$this->response(
			'rtdi_import_menus',
			$fileExists ? esc_html__( 'Setting menus', 'radius-demo-importer' ) : '',
			$fileExists ? esc_html__( 'Customizer settings imported', 'radius-demo-importer' ) : esc_html__( 'No customizer settings found', 'radius-demo-importer' )
		);
	}
}
