<?php
/**
 * Plugin Setup Helpers.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Helpers;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Plugin Setup Helpers.
 */
class Setup {
	/**
	 * Run only once after plugin is activated.
	 *
	 * @return void
	 */
	public static function activation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// Clear the permalinks.
		flush_rewrite_rules();
	}

	/**
	 * Run only once after plugin is deactivated.
	 *
	 * @return void
	 */
	public static function deactivation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// Clear the permalinks.
		flush_rewrite_rules();
	}
}
