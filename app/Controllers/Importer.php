<?php
/**
 * Import Controller Class.
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
 * Import Controller Class.
 */
class Importer extends Controller {
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
		$this->classes[] = Importer\Content::class;
//		$this->classes[] = Importer\CustomizerImporter::class;
//		$this->classes[] = Importer\WidgetsImporter::class;

		return $this->classes;
	}
}
