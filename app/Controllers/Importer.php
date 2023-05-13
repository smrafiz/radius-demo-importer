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
	 * @return void|array
	 */
	public function services() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Services.
		$this->classes[] = Ajax\Initialize::class;
		$this->classes[] = Ajax\InstallPlugins::class;
		$this->classes[] = Ajax\DownloadFiles::class;
		$this->classes[] = Ajax\InstallDemo::class;
		$this->classes[] = Ajax\CustomizerImport::class;
		$this->classes[] = Ajax\ImportMenus::class;
		$this->classes[] = Ajax\ImportSettings::class;
		$this->classes[] = Ajax\ImportFluentForms::class;
		$this->classes[] = Ajax\ImportWidgets::class;
		$this->classes[] = Ajax\Finalize::class;

		return $this->classes;
	}
}
