<?php
/**
 * Content Import Class.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Controllers\Importer;

use RT\DemoImporter\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Content Import Class.
 */
class Content {
	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * The Demo Configuration.
	 *
	 * @var string
	 */
	public $config;

	/**
	 * The directory for uploading files.
	 *
	 * @var string
	 */
	public $uploadsDir;

	/**
	 * Plugin installation count.
	 *
	 * @var int
	 */
	public $pluginInstallCount;

	/**
	 * Plugin activation count.
	 *
	 * @var int
	 */
	public $pluginActiveCount;

	/**
	 * The response from AJAX calls.
	 *
	 * @var array
	 */
	public $ajaxResponse = [];

	/**
	 * Class init.
	 *
	 * @return void
	 */
	public function register() {
		$this->uploadsDir         = wp_get_upload_dir();
		$this->pluginInstallCount = 0;
		$this->pluginActiveCount  = 0;
		$this->config             = radiusDemoImporter()->config;
	}
}
