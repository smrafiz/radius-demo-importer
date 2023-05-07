<?php
/**
 * Plugin Install Ajax Class.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Controllers\Ajax;

use Plugin_Upgrader;
use WP_Ajax_Upgrader_Skin;
use RT\DemoImporter\Helpers\Fns;
use RT\DemoImporter\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Plugin Install Ajax Class.
 */
class InstallPlugins extends Ajax {
	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * Install count.
	 *
	 * @var int
	 */
	public $installCount;

	/**
	 * Active count.
	 *
	 * @var int
	 */
	public $activeCount;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	public function register() {
		parent::register();

		$this->installCount = 0;
		$this->activeCount  = 0;

		add_action( 'wp_ajax_rtdi_install_plugins', [ $this, 'installCallback' ] );
		add_action( 'wp_ajax_rtdi_activate_plugins', [ $this, 'activateCallback' ] );
	}

	/**
	 * Install plugins ajax callback.
	 *
	 * @return void
	 */
	public function installCallback() {
		Fns::verifyAjaxCall();

		// Install Required Plugins.
		$this->installPlugins( $this->demoSlug );

		// Response.
		$this->response(
			'rtdi_activate_plugins',
			esc_html__( 'Activating required plugins', 'radius-demo-importer' ),
			$this->installCount > 0 ? esc_html__( 'All the required plugins installed', 'radius-demo-importer' ) : esc_html__( 'No plugin required to install', 'radius-demo-importer' )
		);
	}

	/**
	 * Activate plugins ajax callback.
	 *
	 * @return void
	 */
	public function activateCallback() {
		Fns::verifyAjaxCall();

		// Activate Required Plugins.
		$this->activatePlugins( $this->demoSlug );

		// Response.
		$this->response(
			'rtdi_download_demo_files',
			esc_html__( 'Downloading demo files', 'radius-demo-importer' ),
			$this->activeCount > 0 ? esc_html__( 'All the required plugins activated', 'radius-demo-importer' ) : esc_html__( 'No plugin required to activate', 'radius-demo-importer' )
		);
	}

	/**
	 * Installing required plugins.
	 *
	 * @param string $slug Plugin slug.
	 *
	 * @return void
	 */
	private function installPlugins( $slug ) {
		$plugins = $this->config[ $slug ]['plugins'];

		foreach ( $plugins as $pluginSlug => $plugin ) {
			$name     = ! empty( $plugin['name'] ) ? $plugin['name'] : '';
			$source   = ! empty( $plugin['source'] ) ? $plugin['source'] : '';
			$filePath = ! empty( $plugin['filePath'] ) ? $plugin['filePath'] : '';
			$location = ! empty( $plugin['location'] ) ? $plugin['location'] : '';

			if ( 'WordPress' === $source ) {
				$this->installOrgPlugin( $filePath, $pluginSlug );
			} else {
				$this->installCustomPlugin( $filePath, $location );
			}
		}
	}

	/**
	 * Activating required plugins.
	 *
	 * @param string $slug Plugin slug.
	 *
	 * @return void
	 */
	private function activatePlugins( $slug ) {
		$plugins = $this->config[ $slug ]['plugins'];

		foreach ( $plugins as $pluginSlug => $plugin ) {
			$name         = ! empty( $plugin['name'] ) ? $plugin['name'] : '';
			$filePath     = ! empty( $plugin['filePath'] ) ? $plugin['filePath'] : '';
			$pluginStatus = $this->pluginStatus( $filePath );

			if ( 'inactive' === $pluginStatus ) {
				$this->activatePlugin( $filePath );
				$this->activeCount ++;
			}
		}
	}

	/**
	 * Check if plugin is active or not.
	 *
	 * @param string $path Plugin path.
	 *
	 * @return string
	 */
	public function pluginStatus( $path ) {
		$status = 'install';

		$pluginPath = WP_PLUGIN_DIR . '/' . $path;

		if ( file_exists( $pluginPath ) ) {
			$status = is_plugin_active( $path ) ? 'active' : 'inactive';
		}

		return $status;
	}

	/**
	 * Installing wordpress.org Plugin.
	 *
	 * @param string $path Plugin path.
	 * @param string $slug Plugin slug.
	 *
	 * @return void
	 */
	public function installOrgPlugin( $path, $slug ) {
		$pluginStatus = $this->pluginStatus( $path );

		if ( 'install' === $pluginStatus ) {
			// Include required libs for installation.
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
			require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

			// Get Plugin Info.
			$api = $this->callPluginApi( $slug );

			$skin     = new WP_Ajax_Upgrader_Skin();
			$upgrader = new Plugin_Upgrader( $skin );
			$upgrader->install( $api->download_link );

			$this->installCount ++;
		}
	}

	/**
	 * Plugin API.
	 *
	 * @param string $slug Plugin slug.
	 *
	 * @return array|object|\WP_Error
	 */
	public function callPluginApi( $slug ) {
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		return plugins_api(
			'plugin_information',
			[
				'slug'   => $slug,
				'fields' => [
					'downloaded'        => false,
					'rating'            => false,
					'description'       => false,
					'short_description' => false,
					'donate_link'       => false,
					'tags'              => false,
					'sections'          => false,
					'homepage'          => false,
					'added'             => false,
					'last_updated'      => false,
					'compatibility'     => false,
					'tested'            => false,
					'requires'          => false,
					'downloadlink'      => true,
					'icons'             => false,
				],
			]
		);
	}

	/**
	 * Activating plugin.
	 *
	 * @param string $path Plugin path.
	 *
	 * @return void
	 */
	public function activatePlugin( $path ) {
		if ( $path ) {
			$activate = activate_plugin( $path, '', false, true );
		}
	}

	/**
	 * Installing custom Plugin.
	 *
	 * @param string $path Plugin path.
	 * @param string $externalUrl Plugin external URL.
	 *
	 * @return void
	 */
	public function installCustomPlugin( $path, $externalUrl ) {

		$plugin_status = $this->pluginStatus( $path );

		if ( 'install' === $plugin_status ) {
			// Make sure we have the dependency.
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			/*
			 * Initialize WordPress' file system handler.
			 *
			 * @var WP_Filesystem_Base $wp_filesystem
			 */
			WP_Filesystem();

			global $wp_filesystem;

			$plugin = $this->demoUploadDir() . 'plugin.zip';

			$file = wp_remote_retrieve_body(
				wp_remote_get(
					$externalUrl,
					[
						'timeout' => 60,
					]
				)
			);

			$wp_filesystem->mkdir( $this->demoUploadDir() );
			$wp_filesystem->put_contents( $plugin, $file );

			// Unzip file.
			unzip_file( $plugin, WP_PLUGIN_DIR );

			$plugin_file = WP_PLUGIN_DIR . '/' . esc_html( $path );

			// Delete zip.
			$wp_filesystem->delete( $plugin );

			$this->installCount ++;
		}
	}
}
