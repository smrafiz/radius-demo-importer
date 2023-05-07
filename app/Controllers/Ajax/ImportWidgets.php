<?php
/**
 * Widgets Import Ajax Class.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Controllers\Ajax;

use RT\DemoImporter\Helpers\Fns;
use RT\DemoImporter\Traits\SingletonTrait;
use RT\DemoImporter\Controllers\Models\Widgets;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Widgets Import Ajax Class.
 */
class ImportWidgets extends Ajax {
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

		add_action( 'wp_ajax_rtdi_import_widgets', [ $this, 'import' ] );
	}

	/**
	 * Widgets import process callback.
	 *
	 * @return void
	 */
	public function import() {
		Fns::verifyAjaxCall();

		$widgetsFilePath = $this->demoUploadDir( $this->demoSlug ) . '/widget.wie';
		$fileExists      = file_exists( $widgetsFilePath );

		if ( $fileExists ) {
			ob_start();

			// Import widgets data.
			( new Widgets() )->import( $widgetsFilePath );

			ob_end_clean();
		}

		$sliderFileExists = file_exists( $this->demoUploadDir( $this->demoSlug ) . '/revslider.zip' );

		$this->response(
			$sliderFileExists ? 'rtdi_import_revslider' : 'rtdi_finalize_demo',
			$sliderFileExists ? esc_html__( 'Importing Revolution slider', 'radius-demo-importer' ) : esc_html__( 'Finalizing demo data', 'radius-demo-importer' ),
			$fileExists ? esc_html__( 'Widgets imported', 'radius-demo-importer' ) : esc_html__( 'No widgets found', 'radius-demo-importer' )
		);
	}
}
