<?php
/**
 * A model class that extends WP_Customize_Setting, so we can access
 * the protected updated method when importing options.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Controllers\Models;

use WP_Customize_Setting;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * A class that extends WP_Customize_Setting.
 */
final class CustomizerOption extends WP_Customize_Setting {

	/**
	 * Import an option value for this setting.
	 *
	 * @param mixed $value The option value.
	 *
	 * @return void
	 */
	public function import( $value ) {
		$this->update( $value );
	}
}
