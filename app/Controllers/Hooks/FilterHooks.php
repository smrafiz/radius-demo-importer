<?php
/**
 * Filter Hooks Class.
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
 * Filter Hooks Class.
 */
class FilterHooks {
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
		\add_filter( 'upload_mimes', [ __CLASS__, 'supportedFileTypes' ] );
	}

	/**
	 * Update the list of file types support.
	 *
	 * @param array $file_types List of supported file types.
	 *
	 * @return array Updated list of supported file types.
	 */
	public static function supportedFileTypes( $file_types ) {
		$new_filetypes        = [];
		$new_filetypes['svg'] = 'image/svg+xml';

		return array_merge( $file_types, $new_filetypes );
	}
}
