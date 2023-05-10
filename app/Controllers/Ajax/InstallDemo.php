<?php
/**
 * Install Demo Ajax Class.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Controllers\Ajax;

use RTDI_WP_Import;
use RT\DemoImporter\Helpers\Fns;
use RT\DemoImporter\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Start Demo Process Ajax Class.
 */
class InstallDemo extends Ajax {
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

		add_action( 'wp_ajax_rtdi_import_xml', [ $this, 'install' ] );
	}

	/**
	 * Install demo process callback.
	 *
	 * @return void
	 */
	public function install() {
		Fns::verifyAjaxCall();

		$xmlFile = $this->demoUploadDir( $this->demoSlug ) . '/content.xml';

		$fileExists = file_exists( $xmlFile );

		// Init import actions.
		$this->beforeImportActions( $xmlFile, $this->excludeImages );

		if ( $fileExists ) {
			$this->importDemoContent( $xmlFile, $this->excludeImages );
		}

		// Response.
		$this->response(
			$fileExists ? 'rtdi_import_customizer' : '',
			$fileExists ? esc_html__( 'Importing customizer settings', 'radius-demo-importer' ) : '',
			$fileExists ? esc_html__( 'All content imported', 'radius-demo-importer' ) : '',
			! $fileExists,
			! $fileExists ? esc_html__( 'Demo import process failed. No content file found', 'radius-demo-importer' ) : '',
		);
	}

	/**
	 * Import demo content.
	 *
	 * @param string $xmlFilePath XML file path.
	 * @param string $excludeImages Exclude images.
	 *
	 * @return void
	 */
	private function importDemoContent( $xmlFilePath, $excludeImages ) {
		if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
			define( 'WP_LOAD_IMPORTERS', true );
		}

		if ( ! class_exists( 'RTDI_WP_Import' ) ) {
			$wpImporter = RTDI_PATH . 'lib/wordpress-importer/wordpress-importer.php';

			if ( file_exists( $wpImporter ) ) {
				require_once $wpImporter;
			}
		}

		// Import demo content from XML.
		if ( class_exists( 'RTDI_WP_Import' ) ) {
			$excludeImages    = ! ( 'true' === $excludeImages );
			$homeSlug         = $this->demoSlug;
			$blogSlug         = '';
			$elementorKitSlug = '';

			if ( ! empty( $this->config['blogSlug'] ) || ! empty( $this->config['demoData'][ $this->demoSlug ]['blogSlug'] ) ) {
				$blogSlug = $this->multiple ? $this->config['demoData'][ $this->demoSlug ]['blogSlug'] : $this->config['blogSlug'];
			}

			if ( ! empty( $this->config['elementorKitSlug'] ) || ! empty( $this->config['demoData'][ $this->demoSlug ]['elementorKitSlug'] ) ) {
				$elementorKitSlug = $this->multiple ? $this->config['demoData'][ $this->demoSlug ]['elementorKitSlug'] : $this->config['elementorKitSlug'];
			}

			if ( file_exists( $xmlFilePath ) ) {
				$wp_import                    = new RTDI_WP_Import();
				$wp_import->fetch_attachments = $excludeImages;

				ob_start();

				// Import XML.
				$wp_import->import( $xmlFilePath );

				ob_end_clean();

				if ( ! $excludeImages ) {
					$this->unsetThumbnails();
				}

				// Setting front page.
				if ( $homeSlug ) {
					$page = get_page_by_path( $homeSlug );

					if ( $page ) {
						update_option( 'show_on_front', 'page' );
						update_option( 'page_on_front', $page->ID );
					} else {
						$page = Fns::getPageByTitle( 'Home' );

						if ( $page ) {
							update_option( 'show_on_front', 'page' );
							update_option( 'page_on_front', $page->ID );
						}
					}
				}

				// Setting blog page.
				if ( $blogSlug ) {
					$blog = get_page_by_path( $blogSlug );

					if ( $blog ) {
						update_option( 'show_on_front', 'page' );
						update_option( 'page_for_posts', $blog->ID );
					}
				}

				if ( ! $homeSlug && ! $blogSlug ) {
					update_option( 'show_on_front', 'posts' );
				}

				// Settings Elementor Kit.
				if ( $elementorKitSlug ) {
					$elementorKit = get_page_by_path( $elementorKitSlug, OBJECT, 'elementor_library' );

					if ( $elementorKit ) {
						update_option( 'elementor_active_kit', $elementorKit->ID );
					}
				}
			}
		}
	}

	/**
	 * Unset featured images from posts.
	 *
	 * @return void
	 */
	private function unsetThumbnails() {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key = %s", '_thumbnail_id' )
		);
	}
}
