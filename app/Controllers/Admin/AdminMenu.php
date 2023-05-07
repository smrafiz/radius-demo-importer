<?php
/**
 * Admin Menu Class.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Controllers\Admin;

use RT\DemoImporter\Helpers\Fns;
use RT\DemoImporter\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Admin Menu Class.
 */
class AdminMenu {
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
		add_action( 'admin_menu', [ $this, 'registerAdminMenu' ] );
		add_action( 'admin_init', [ $this, 'removeAllNotices' ] );
	}

	/**
	 * Add demo importer sub menu.
	 *
	 * @return void
	 */
	public function registerAdminMenu() {
		add_submenu_page(
			'themes.php',
			esc_html__( 'Radius Demo Importer', 'radius-demo-importer' ),
			esc_html__( 'Install Demo Content', 'radius-demo-importer' ),
			'manage_options',
			'rtdi-demo-importer',
			[
				$this,
				'renderDemoImportPage',
			]
		);
	}

	/**
	 * Render the demo import page.
	 *
	 * @return void
	 */
	public function renderDemoImportPage() {
		$themeConfig     = radiusDemoImporter()->config;
		$activeTheme     = radiusDemoImporter()->activeTheme();
		$supportedThemes = radiusDemoImporter()->supportedThemes();

		if ( ! in_array( $activeTheme, $supportedThemes, true ) ) {
			$themeConfig = [];
		}

		Fns::renderView( 'demo-import', [ 'themeConfig' => $themeConfig ] );
	}

	/**
	 * Remove all admin notices from the demo import page.
	 *
	 * @return void
	 */
	public function removeAllNotices() {
		global $pagenow;

		if ( 'themes.php' === $pagenow && isset( $_GET['page'] ) && ( 'rtdi-demo-importer' === $_GET['page'] || 'rt-demo-importer-status' === $_GET['page'] ) ) {
			remove_all_actions( 'admin_notices' );
		}
	}
}
