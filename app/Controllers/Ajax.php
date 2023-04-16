<?php
/**
 * Ajax Controller Class.
 *
 * @package DemoImporter
 */

namespace RT\DemoImporter\Controllers;

use RT\DemoImporter\Abstracts\Controller;
use RT\DemoImporter\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Ajax Controller Class.
 */
class Ajax extends Controller {
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
		$this
			->admin_ajax()
			->frontend_ajax();

		return $this->classes;
	}

	/**
	 * Admin Ajax
	 *
	 * @return Object
	 */
	private function admin_ajax() {
		// $this->classes[] = AdminAjax\Preview::class;
		// $this->classes[] = AdminAjax\Settings::class;
		// $this->classes[] = AdminAjax\ShortcodeSource::class;
		// $this->classes[] = AdminAjax\Shortcode::class;

		return $this;
	}

	/**
	 * Frontend Ajax
	 *
	 * @return Object
	 */
	private function frontend_ajax() {
		return $this;
	}
}
