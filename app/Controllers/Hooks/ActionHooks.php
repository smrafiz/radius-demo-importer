<?php
/**
 * Action Hooks Class.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Controllers\Hooks;

use RT\DemoImporter\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Action Hooks Class.
 */
class ActionHooks {
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
		add_action( 'init', [ $this, 'rewriteFlushCheck' ] );
	}

	/**
	 * Check if the option to flush the rewrite rules has been set, and if so, flushes them and deletes the option.
	 *
	 * @return void
	 */
	public function rewriteFlushCheck() {
		$theme  = str_replace( '-', '_', radiusDemoImporter()->activeTheme() );
		$option = $theme . '_rtdi_importer_rewrite_flash';

		if ( 'true' === get_option( $option ) ) {
			flush_rewrite_rules();
			delete_option( $option );
		}
	}
}
