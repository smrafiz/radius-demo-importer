<?php
/**
 * Demo Download Ajax Class.
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
 * Start Demo Process Ajax Class.
 */
class DownloadFiles extends Ajax {
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

		add_action( 'wp_ajax_rtdi_download_demo_files', [ $this, 'download' ] );
	}

	/**
	 * Download process callback.
	 *
	 * @return void
	 */
	public function download() {
		Fns::verifyAjaxCall();

		$downloads = $this->multiple ? $this->downloadDemoFiles( Fns::keyExists( $this->config['demoData'][ $this->demoSlug ]['demoZip'] ) ) : $this->downloadDemoFiles( Fns::keyExists( $this->config['demoZip'] ) );

		// Response.
		$this->response(
			$downloads ? 'rtdi_import_xml' : '',
			$downloads ? esc_html__( 'Importing posts, pages and medias. It may take a bit longer time', 'radius-demo-importer' ) : '',
			$downloads ? esc_html__( 'All demo files downloaded', 'radius-demo-importer' ) : '',
			! $downloads,
			! $downloads ? esc_html__( 'Demo import process failed. Demo files can not be downloaded', 'radius-demo-importer' ) : '',
		);
	}

	/**
	 * Download demo files.
	 *
	 * @param string $external_url External demo URL.
	 *
	 * @return bool
	 */
	public function downloadDemoFiles( $external_url ) {
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

		$result = true;

		if ( ! ( $wp_filesystem->exists( $this->demoUploadDir() ) ) ) {
			$result = $wp_filesystem->mkdir( $this->demoUploadDir() );
		}

		// Abort the request if the local uploads directory couldn't be created.
		if ( ! $result ) {
			return false;
		} else {
			$demoData = $this->demoUploadDir() . 'imported-demo-data.zip';

			$response = wp_remote_get(
				$external_url,
				[
					'timeout' => 60,
				]
			);

			if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
				return false;
			}

			$file = wp_remote_retrieve_body( $response );

			$wp_filesystem->put_contents( $demoData, $file );

			// Unzip file.
			unzip_file( $demoData, $this->demoUploadDir() );

			// Delete zip.
			$wp_filesystem->delete( $demoData );

			return true;
		}
	}
}
