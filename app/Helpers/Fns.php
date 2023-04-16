<?php
/**
 * Plugin Helpers.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Helpers;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Plugin Helpers.
 */
class Fns {
	/**
	 * Loop through the classes, initialize them,
	 * and call the register() method if it exists.
	 *
	 * @param array $services Services array.
	 *
	 * @return void
	 */
	public static function initServices( $services ) {
		if ( empty( $services ) ) {
			return;
		}

		foreach ( $services as $service ) {
			$class = $service::instance();

			if ( method_exists( $class, 'register' ) ) {
				$class->register();
			}
		}
	}

	/**
	 * Render resources view.
	 *
	 * @param string  $viewName View name.
	 * @param array   $args View args.
	 * @param boolean $return View return.
	 *
	 * @return string|void
	 */
	public static function renderView( $viewName, $args = [], $return = false ) {
		$viewName = str_replace( '.', '/', $viewName );

		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args );
		}

		$view_file = radiusDemoImporter()->pluginPath() . '/resources/' . $viewName . '.php';

		if ( ! file_exists( $view_file ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', esc_html( $view_file ) ), '1.0.0' );

			return;
		}

		if ( $return ) {
			ob_start();
			include $view_file;

			return ob_get_clean();
		} else {
			include $view_file;
		}
	}
}
