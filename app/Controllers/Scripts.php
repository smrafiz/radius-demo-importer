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
	protected function register() {
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
	 * Registers Admin scripts.
	 *
	 * @return void
	 */
	public function registerAdminScripts() {
		/**
		 * Styles.
		 */
		wp_register_style( 'rtdi-admin-app', radiusDemoImporter()->getAssetsUri( 'css/backend/admin-settings.css' ), '', $this->version );

		/**
		 * Scripts.
		 */
		wp_register_script( 'rtdi-admin-app', radiusDemoImporter()->getAssetsUri( 'js/backend/admin-settings.js' ), '', $this->version, true );

	}

	/**
	 * Enqueues admin scripts.
	 *
	 * @param string $hook Hooks.
	 *
	 * @return void
	 */
	public function enqueueAdminScripts( $hook ) {
		if ( 'rtdi-settings' === $hook ) {
			/**
			 * Styles.
			 */
			wp_enqueue_style( 'rtdi-admin-app' );

			/**
			 * Scripts.
			 */
			wp_enqueue_script( 'rtdi-admin-app' );
			wp_localize_script(
				'rtdi-admin-app',
				'rtdiParams',
				[
					'ajaxurl' => esc_url( $this->ajaxurl ),
					'nonce'   => wp_create_nonce( \radiusDemoImporter()->nonceText ),
				]
			);
		}
	}
}
