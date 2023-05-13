<?php
/**
 * Initialize Demo Process Ajax Class.
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
 * Initialize Demo Process Ajax Class.
 */
class Initialize extends Ajax {
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

		add_action( 'wp_ajax_rtdi_install_demo', [ $this, 'start' ] );
	}

	/**
	 * Start process.
	 *
	 * @return void
	 */
	public function start() {
		Fns::verifyAjaxCall();

		// Resetting database.
		if ( $this->reset ) {
			$this->databaseReset();
		}

		// Importer Init Action hook.
		Fns::doAction( 'rtdi/importer/before_import', $this );

		// Response.
		$this->response(
			'rtdi_install_plugins',
			esc_html__( 'Installing required plugins', 'radius-demo-importer' ),
			( $this->reset ) ? esc_html__( 'Database reset completed', 'radius-demo-importer' ) : esc_html__( 'Minor cleanups completed', 'radius-demo-importer' )
		);
	}

	/**
	 * Database reset.
	 *
	 * @return void
	 */
	private function databaseReset() {
		global $wpdb;

		$coreTables    = [
			'commentmeta',
			'comments',
			'links',
			'postmeta',
			'posts',
			'term_relationships',
			'term_taxonomy',
			'termmeta',
			'terms',
		];
		$excludeTables = [ 'options', 'usermeta', 'users' ];

		$coreTables = array_map(
			function ( $tbl ) {
				global $wpdb;

				return $wpdb->prefix . $tbl;
			},
			$coreTables
		);

		$excludeTables = array_map(
			function ( $tbl ) {
				global $wpdb;

				return $wpdb->prefix . $tbl;
			},
			$excludeTables
		);

		$customTables = [];
		$tableStatus  = $wpdb->get_results( 'SHOW TABLE STATUS' );

		if ( is_array( $tableStatus ) ) {
			foreach ( $tableStatus as $index => $table ) {
				if ( 0 !== stripos( $table->Name, $wpdb->prefix ) ) {
					continue;
				}

				if ( empty( $table->Engine ) ) {
					continue;
				}

				if ( false === in_array( $table->Name, $coreTables ) && false === in_array( $table->Name, $excludeTables ) ) {
					$customTables[] = $table->Name;
				}
			}
		}

		$customTables = array_merge( $coreTables, $customTables );

		foreach ( $customTables as $tbl ) {
			$wpdb->query( 'SET foreign_key_checks = 0' );
			$wpdb->query( 'TRUNCATE TABLE ' . $tbl );
		}

		// Delete Widgets.
		Fns::deleteWidgets();

		// Delete ThemeMods.
		Fns::deleteThemeMods();

		// Clear "uploads" folder.
		$this->clearUploads( $this->uploadsDir['basedir'] );
	}

	/**
	 * Clear folder
	 *
	 * @param string $dir Directory to clean.
	 *
	 * @return bool
	 */
	private function clearUploads( $dir ) {
		$files = array_diff( scandir( $dir ), [ '.', '..' ] );

		foreach ( $files as $file ) {
			( is_dir( "$dir/$file" ) ) ? $this->clearUploads( "$dir/$file" ) : unlink( "$dir/$file" );
		}

		return ! ( $dir !== $this->uploadsDir['basedir'] ) || rmdir( $dir );
	}
}
