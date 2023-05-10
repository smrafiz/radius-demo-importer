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

		$viewFile = radiusDemoImporter()->pluginPath() . '/resources/' . $viewName . '.php';

		if ( ! file_exists( $viewFile ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', esc_html( $viewFile ) ), '1.0.0' );

			return;
		}

		if ( $return ) {
			ob_start();
			include $viewFile;

			return ob_get_clean();
		} else {
			include $viewFile;
		}
	}

	/**
	 * Determines the active status of a plugin given its file path.
	 *
	 * @param string $filePath The file path of the plugin.
	 *
	 * @return string
	 */
	public static function pluginActivationStatus( $filePath ) {
		$status     = 'install';
		$pluginPath = WP_PLUGIN_DIR . '/' . esc_attr( $filePath );

		if ( file_exists( $pluginPath ) ) {
			$status = is_plugin_active( $filePath ) ? 'active' : 'inactive';
		}

		return $status;
	}

	/**
	 * Returns the display status of a plugin given its status code.
	 *
	 * @param string $status The status code of the plugin.
	 *
	 * @return string
	 */
	public static function getPluginStatus( $status ) {
		switch ( $status ) {
			case 'install':
				$pluginStatus = esc_html__( 'Not Installed', 'radius-demo-importer' );
				break;

			case 'active':
				$pluginStatus = esc_html__( 'Installed and Active', 'radius-demo-importer' );
				break;

			case 'inactive':
				$pluginStatus = esc_html__( 'Installed but Not Active', 'radius-demo-importer' );
				break;

			default:
				$pluginStatus = '';
		}

		return $pluginStatus;
	}

	/**
	 * Check if the AJAX call is valid.
	 *
	 * @return void
	 */
	public static function verifyAjaxCall() {
		check_ajax_referer( radiusDemoImporter()->nonceText, radiusDemoImporter()->nonceId );

		if ( ! current_user_can( 'import' ) ) {
			wp_die(
				sprintf(
				/* translators: %1$s - opening div and paragraph HTML tags, %2$s - closing div and paragraph HTML tags. */
					__( '%1$sYour user role isn\'t high enough. You don\'t have permission to import demo data.%2$s', 'radius-demo-importer' ),
					'<div class="notice notice-error"><p>',
					'</p></div>'
				)
			);
		}
	}

	/**
	 * Delete widgets.
	 *
	 * @return void
	 */
	public static function deleteWidgets() {
		global $wp_registered_widget_controls;

		$widgetControls = $wp_registered_widget_controls;

		$availableWidgets = [];

		foreach ( $widgetControls as $widget ) {
			if ( ! empty( $widget['id_base'] ) && ! isset( $availableWidgets[ $widget['id_base'] ] ) ) {
				$availableWidgets[] = $widget['id_base'];
			}
		}

		update_option( 'sidebars_widgets', [ 'wp_inactive_widgets' => [] ] );

		foreach ( $availableWidgets as $widgetData ) {
			update_option( 'widget_' . $widgetData, [] );
		}
	}

	/**
	 * Delete ThemeMods.
	 *
	 * @return void
	 */
	public static function deleteThemeMods() {
		$themeSlug = get_option( 'stylesheet' );
		$mods      = get_option( "theme_mods_$themeSlug" );

		if ( false !== $mods ) {
			delete_option( "theme_mods_$themeSlug" );
		}
	}

	/**
	 * Deletes any registered navigation menus
	 *
	 * @return void
	 */
	public static function deleteNavMenus() {
		$nav_menus = wp_get_nav_menus();

		// Delete navigation menus.
		if ( ! empty( $nav_menus ) ) {
			foreach ( $nav_menus as $nav_menu ) {
				wp_delete_nav_menu( $nav_menu->slug );
			}
		}
	}

	/**
	 * Check if array key exists;
	 *
	 * @param string $key Key to check.
	 * @param string $dataType Data type.
	 *
	 * @return array|string
	 */
	public static function keyExists( $key, $dataType = 'string' ) {
		if ( 'array' === $dataType ) {
			$data = [];
		} else {
			$data = '';
		}

		return ! empty( $key ) ? $key : $data;
	}

	/**
	 * Get page by title.
	 *
	 * @param string $title Page name.
	 * @param string $post_type Post type.
	 *
	 * @return \WP_Post|null
	 */
	public static function getPageByTitle( $title, $post_type = 'page' ) {
		$query = new \WP_Query(
			[
				'post_type'              => esc_html( $post_type ),
				'title'                  => esc_html( $title ),
				'post_status'            => 'all',
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'orderby'                => 'post_date ID',
				'order'                  => 'ASC',
			]
		);

		if ( ! empty( $query->post ) ) {
			$pageByTitle = $query->post;
		} else {
			$pageByTitle = null;
		}

		return $pageByTitle;
	}

	/**
	 * Hook Helper: do_action.
	 *
	 * @param string $hookName The action hook name.
	 * @param mixed  ...$arg Additional arguments. Default empty.
	 */
	public static function doAction( $hookName, ...$arg ) {
		if ( has_action( $hookName ) ) {
			do_action( esc_html( $hookName ), ...$arg );
		}
	}
}
