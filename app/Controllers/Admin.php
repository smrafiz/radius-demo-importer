<?php
/**
 * Admin Controller Class.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Controllers;

use RT\DemoImporter\Abstracts\Controller;
use RT\DemoImporter\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Admin Controller Class.
 */
class Admin extends Controller {
	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * Classes to include.
	 *
	 * @return array
	 */
	public function services() {
		$this->classes[] = Admin\AdminMenu::class;

		return $this->classes;
	}
}
