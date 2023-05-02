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
	 * Theme demo config.
	 *
	 * @var array
	 */
	public $config = [];

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

		// Theme Config.
		$this->config = radiusDemoImporter()->config;

		// Before import actions.
		$this->beforeImportActions();

		// Services.
		$this->classes[] = Ajax\StartProcess::class;
		$this->classes[] = Ajax\InstallPlugins::class;
		// $this->classes[] = Importer\CustomizerImporter::class;
		// $this->classes[] = Importer\WidgetsImporter::class;

		// After import actions.
		$this->afterImportActions();

		return $this->classes;
	}

	/**
	 * Before import action.
	 *
	 * @return void
	 */
	public function beforeImportActions() {
		\do_action( 'rtdi/before/import', $this );
	}

	/**
	 * After import action.
	 *
	 * @return void
	 */
	public function afterImportActions() {
		\do_action( 'rtdi/after/import', $this );
	}
}
