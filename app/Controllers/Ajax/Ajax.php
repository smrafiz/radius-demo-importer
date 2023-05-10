<?php
/**
 * Ajax Class.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Controllers\Ajax;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Ajax Class.
 */
class Ajax {
	/**
	 * Theme demo config.
	 *
	 * @var array
	 */
	public $config = [];

	/**
	 * Ajax response.
	 *
	 * @var array
	 */
	public $response = [];

	/**
	 * Demo slug.
	 *
	 * @var string
	 */
	public $demoSlug = '';

	/**
	 * Exclude images.
	 *
	 * @var string
	 */
	public $excludeImages = '';

	/**
	 * Multiple Zip check.
	 *
	 * @var bool
	 */
	public $multiple = false;

	/**
	 * Uploads directory.
	 *
	 * @var string
	 */
	public $uploadsDir;

	/**
	 * Database reset.
	 *
	 * @var bool
	 */
	public $reset;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	public function register() {
		// Theme config.
		$this->config = radiusDemoImporter()->config;

		// Uploads Directory.
		$this->uploadsDir = wp_get_upload_dir();

		// Check if multiple demo is configured.
		$this->multiple = ! empty( $this->config['multipleZip'] ) ? $this->config['multipleZip'] : false;

		// First demo slug.
		$firstDemoSlug = array_key_first( $this->config['demoData'] );

		// Demo slug.
		$this->demoSlug = ! empty( $_POST['demo'] ) ? $this->multiple ? sanitize_text_field( wp_unslash( $_POST['demo'] ) ) : $firstDemoSlug : $firstDemoSlug;

		// Check if images import is needed.
		$this->excludeImages = ! empty( $_POST['excludeImages'] ) ? sanitize_text_field( wp_unslash( $_POST['excludeImages'] ) ) : '';

		// Check if database reset needed.
		$this->reset = isset( $_POST['reset'] ) && 'true' === $_POST['reset'];
	}

	/**
	 * Prepare ajax response.
	 *
	 * @param string $nextPhase Next phase.
	 * @param string $nextPhaseMessage Next phase message.
	 * @param string $complete Completed message.
	 * @param bool   $error Error.
	 * @param string $errorMessage Error message.
	 *
	 * @return void
	 */
	public function response( $nextPhase, $nextPhaseMessage, $complete = '', $error = false, $errorMessage = '' ) {
		$this->response = [
			'demo'             => $this->demoSlug,
			'excludeImages'    => $this->excludeImages,
			'nextPhase'        => $nextPhase,
			'nextPhaseMessage' => $nextPhaseMessage,
			'completedMessage' => $complete,
			'error'            => $error,
			'errorMessage'     => $errorMessage,
		];

		$this->sendResponse();
	}

	/**
	 * Send ajax response.
	 *
	 * @return void
	 */
	private function sendResponse() {
		$json = wp_json_encode( $this->response );

		wp_send_json( $json );
	}

	/**
	 * Demo upload path.
	 *
	 * @param string $path Path.
	 *
	 * @return string
	 */
	public function demoUploadDir( $path = '' ) {
		return $this->uploadsDir['basedir'] . '/imported-demo-data/' . $path;
	}

	/**
	 * Init import action.
	 *
	 * @return void
	 */
	public function initImportActions( $name, ...$args ) {
		do_action( 'rtdi/importer/init' );
	}

	/**
	 * Before import action.
	 *
	 * @param string $xml Demo XML file.
	 * @param string $excludeImages $exclude images.
	 *
	 * @return void
	 */
	public function beforeImportActions( $xml, $excludeImages ) {
		do_action( 'rtdi/importer/before/import', $xml, $excludeImages );
	}

	/**
	 * After import action.
	 *
	 * @return void
	 */
	public function afterImportActions() {
		do_action( 'rtdi/importer/after/import' );
	}
}
