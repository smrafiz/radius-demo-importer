<?php
/**
 * Abstract Class for Controller.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Abstracts;

use RT\DemoImporter\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Abstract Class for Controller.
 */
abstract class Controller {
	/**
	 * Classes.
	 *
	 * @var array
	 */
	protected $classes;

	/**
	 * Services to include.
	 *
	 * @return array
	 */
	abstract public function services();

	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register() {
		Fns::initServices( $this->services() );
	}
}
