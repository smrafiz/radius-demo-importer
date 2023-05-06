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
	 * Uploads directory.
	 *
	 * @var string
	 */
	public $uploadsDir;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	public function register() {
		$this->uploadsDir    = wp_get_upload_dir();
		$this->demoSlug      = ! empty( $_POST['demo'] ) ? sanitize_text_field( wp_unslash( $_POST['demo'] ) ) : '';
		$this->excludeImages = ! empty( $_POST['excludeImages'] ) ? sanitize_text_field( wp_unslash( $_POST['excludeImages'] ) ) : '';
		$this->config        = radiusDemoImporter()->config;
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
		return $this->uploadsDir['basedir'] . '/demo-pack/' . $path;
	}

	/**
	 * Before import action.
	 *
	 * @return void
	 */
	public function beforeImportActions() {
		\do_action( 'rtdi/before/import' );
	}

	/**
	 * After import action.
	 *
	 * @return void
	 */
	public function afterImportActions() {
		\do_action( 'rtdi/after/import' );
	}
}
