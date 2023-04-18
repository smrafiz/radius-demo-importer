<?php
/**
 * Scripts Controller Class.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Controllers;

use RT\DemoImporter\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Scripts Controller Class.
 */
class Scripts {
	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Ajax URL
	 *
	 * @var string
	 */
	private $ajaxurl;

	/**
	 * Styles.
	 *
	 * @var array
	 */
	private $styles = [];

	/**
	 * Scripts.
	 *
	 * @var array
	 */
	private $scripts = [];

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	public function register() {
		$this->version = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? time() : RTDI_VERSION;
		$this->ajaxurl = admin_url( 'admin-ajax.php' );

		// Admin scripts.
		$this->adminScripts();
	}

	/**
	 * Admin scripts.
	 *
	 * @return void
	 */
	public function adminScripts() {
		add_action( 'admin_enqueue_scripts', [ $this, 'registerAdminScripts' ], 1 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAdminScripts' ] );
	}

	/**
	 * Get admin scripts.
	 *
	 * @return void
	 */
	public function getAdminScripts() {
		/**
		 * Styles.
		 */
		$this->styles = [
			[
				'handle' => 'rtdi-admin-app',
				'src'    => radiusDemoImporter()->getAssetsUri( 'css/admin.min.css' ),
			],
		];

		/**
		 * Scripts
		 */
		$this->scripts = [
			[
				'handle'  => 'rtdi-imagesloaded',
				'src'     => radiusDemoImporter()->getAssetsUri( 'vendors/imagesloaded.pkgd.min.js' ),
				'version' => '5.0.0',
			],
			[
				'handle'  => 'rtdi-isotope',
				'src'     => radiusDemoImporter()->getAssetsUri( 'vendors/isotope.pkgd.min.js' ),
				'version' => '3.0.6',
			],
			[
				'handle'  => 'rtdi-admin-app',
				'src'     => radiusDemoImporter()->getAssetsUri( 'js/admin.min.js' ),
				'version' => $this->version,
			],
		];
	}

	/**
	 * Registers Admin scripts.
	 *
	 * @return void
	 */
	public function registerAdminScripts() {
		$this->getAdminScripts();

		/**
		 * Styles.
		 */
		foreach ( $this->styles as $style ) {
			wp_register_style( $style['handle'], $style['src'], '', $this->version );
		}

		/**
		 * Scripts.
		 */
		foreach ( $this->scripts as $script ) {
			wp_register_script( $script['handle'], $script['src'], '', $script['version'], true );
		}
	}

	/**
	 * Enqueues admin scripts.
	 *
	 * @param string $hook Hooks.
	 *
	 * @return void
	 */
	public function enqueueAdminScripts( $hook ) {
		if ( 'appearance_page_rtdi-demo-importer' !== $hook ) {
			return;
		}

		/**
		 * Styles.
		 */
		foreach ( $this->styles as $style ) {
			wp_enqueue_style( $style['handle'] );
		}

		/**
		 * Scripts.
		 */
		foreach ( $this->scripts as $script ) {
			wp_enqueue_script( $script['handle'] );
		}

		/**
		 * Localize script.
		 */
		wp_localize_script(
			'rtdi-admin-app',
			'rtdiAdminParams',
			$this->localizeData()
		);
	}

	/**
	 * Localized script data.
	 *
	 * @return array
	 */
	private function localizeData() {
		return [
			'ajaxurl'           => esc_url( $this->ajaxurl ),
			'nonce'             => wp_create_nonce( radiusDemoImporter()->nonceText ),
			'prepare_importing' => esc_html__( 'Preparing to install demo data', 'radius-demo-importer' ),
			'reset_database'    => esc_html__( 'Resetting database', 'radius-demo-importer' ),
			'no_reset_database' => esc_html__( 'Database was not reset', 'radius-demo-importer' ),
			'import_error'      => esc_html__( 'There was an error in importing demo. Please reload the page and try again.', 'radius-demo-importer' ),
			'import_success'    => '<h2>' . esc_html__( 'All done. Have fun!', 'radius-demo-importer' ) . '</h2><p>' . esc_html__( 'Demo data has been successfully installed.', 'radius-demo-importer' ) . '</p><a class="button" target="_blank" href="' . esc_url( home_url( '/' ) ) . '">View your Website</a><a class="button" href="' . esc_url( admin_url( '/admin.php?page=rtdi-demo-importer' ) ) . '">' . esc_html__( 'Go Back', 'radius-demo-importer' ) . '</a>',
		];
	}
}
