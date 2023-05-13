<?php
/**
 * Finalize Import Process Ajax Class.
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
 * Finalize Import Process Ajax Class.
 */
class Finalize extends Ajax {
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

		add_action( 'wp_ajax_rtdi_finalize_demo', [ $this, 'end' ] );
	}

	/**
	 * End process.
	 *
	 * @return void
	 */
	public function end() {
		Fns::verifyAjaxCall();

		// Finalize import actions.
		Fns::doAction( 'rtdi/importer/after_import', $this );

		$this->response(
			'',
			'',
			esc_html__( 'Demo data has been successfully installed.', 'radius-demo-importer' )
		);
	}
}
