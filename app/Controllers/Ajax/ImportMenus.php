<?php
/**
 * Menus Import Ajax Class.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Controllers\Ajax;

use RT\DemoImporter\Helpers\Fns;
use RT\DemoImporter\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Menus Import Ajax Class.
 */
class ImportMenus extends Ajax {
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
		parent::register();

		add_action( 'wp_ajax_rtdi_import_menus', [ $this, 'import' ] );
	}

	/**
	 * Menus import process callback.
	 *
	 * @return void
	 */
	public function import() {
		Fns::verifyAjaxCall();

		$menus = $this->multiple ? Fns::keyExists( $this->config['demoData'][ $this->demoSlug ]['menus'], 'array' ) : Fns::keyExists( $this->config['menus'], 'array' );

		if ( $menus ) {
			$this->setMenu( $menus );
		}

		// Response.
		$this->response(
			'rtdi_import_settings',
			esc_html__( 'Importing settings', 'radius-demo-importer' ),
			$menus ? esc_html__( 'Menus saved', 'radius-demo-importer' ) : esc_html__( 'No menus found', 'radius-demo-importer' )
		);
	}

	/**
	 * Settings menus.
	 *
	 * @param array $menus Menu array.
	 *
	 * @return void
	 */
	private function setMenu( $menus ) {
		if ( empty( $menus ) ) {
			return;
		}

		$locations = get_theme_mod( 'nav_menu_locations' );

		foreach ( $menus as $menuId => $menuName ) {
			$menuExists = wp_get_nav_menu_object( $menuName );

			if ( ! $menuExists ) {
				$menuTermId = wp_create_nav_menu( $menuName );
			} else {
				$menuTermId = $menuExists->term_id;
			}

			$locations[ $menuId ] = $menuTermId;
		}

		set_theme_mod( 'nav_menu_locations', $locations );
	}
}
