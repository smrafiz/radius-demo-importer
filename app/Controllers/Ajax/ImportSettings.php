<?php
/**
 * Settings Import Ajax Class.
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
 * Settings Import Ajax Class.
 */
class ImportSettings extends Ajax {
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

		add_action( 'wp_ajax_rtdi_import_settings', [ $this, 'import' ] );
	}

	/**
	 * Settings import process callback.
	 *
	 * @return void
	 */
	public function import() {
		Fns::verifyAjaxCall();

		$settings = ! empty( $this->config[ $this->demoSlug ]['optionsArray'] ) ? $this->config[ $this->demoSlug ]['optionsArray'] : '';

		$settingsExists = isset( $settings ) && is_array( $settings );

		if ( $settingsExists ) {
			foreach ( $settings as $option ) {
				$optionFile = $this->demoUploadDir( $this->demoSlug ) . '/' . $option . '.json';
				$fileExists = file_exists( $optionFile );

				if ( $fileExists ) {
					$data = file_get_contents( $optionFile );

					if ( $data ) {
						update_option( $option, json_decode( $data, true ) );
					}
				}
			}
		}

		$this->response(
			'rtdi_import_widgets',
			esc_html__( 'Importing widgets', 'radius-demo-importer' ),
			$settingsExists ? esc_html__( 'Theme settings imported', 'radius-demo-importer' ) : esc_html__( 'No theme settings found', 'radius-demo-importer' )
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
