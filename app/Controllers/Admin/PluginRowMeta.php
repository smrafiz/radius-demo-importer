<?php
/**
 * Plugin Row Meta Class.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Controllers\Admin;

use RT\DemoImporter\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Plugin Row Meta Class.
 */
class PluginRowMeta {
	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * Register Admin Menu.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'plugin_action_links_' . RTDI_ACTIVE_FILE_NAME, [ $this, 'addRowMeta' ] );
	}

	/**
	 * Add action link.
	 *
	 * @param array $links Action links.
	 *
	 * @return array
	 */
	public function addRowMeta( $links ) {
		return array_merge(
			[
				'<a href="' . esc_url( admin_url( 'themes.php?page=rtdi-demo-importer' ) ) . '">' . esc_html__( 'Install Demo Data', 'radius-demo-importer' ) . '</a>',
				'<a href="' . esc_url( admin_url( 'themes.php?page=rtdi-demo-importer-status' ) ) . '">' . esc_html__( 'System Status', 'radius-demo-importer' ) . '</a>',
			],
			$links
		);
	}
}
