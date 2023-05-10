<?php
/**
 * Action Hooks Class.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Controllers\Hooks;

use RT\DemoImporter\Helpers\Fns;
use RT\DemoImporter\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Action Hooks Class.
 */
class ActionHooks {
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
		add_action( 'init', [ __CLASS__, 'rewriteFlushCheck' ] );
		add_action( 'rtdi/importer/init', [ __CLASS__, 'beforeImportActions' ] );
	}

	/**
	 * Check if the option to flush the rewrite rules has been set, and if so, flushes them and deletes the option.
	 *
	 * @return void
	 */
	public static function rewriteFlushCheck() {
		$theme  = str_replace( '-', '_', radiusDemoImporter()->activeTheme() );
		$option = $theme . '_rtdi_rewrite_flash';

		if ( 'true' === get_option( $option ) ) {
			flush_rewrite_rules();
			delete_option( $option );
		}
	}

	/**
	 * Executes operations before import.
	 *
	 * @param object $obj Reference object.
	 *
	 * @return void
	 */
	public static function beforeImportActions( $obj ) {
		if ( $obj->reset ) {
			return;
		}

		self::cleanups()
			->deletePages()
			->draftPost();
	}

	/**
	 * Executes a chain of cleanup operations.
	 *
	 * @return static
	 */
	private static function cleanups() {
		// Delete widgets.
		Fns::deleteWidgets();

		// Delete ThemeMods.
		Fns::deleteThemeMods();

		// Delete Nav Menus.
		Fns::deleteNavMenus();

		return new self();
	}

	/**
	 * Deletes some pages.
	 *
	 * @return static
	 */
	private static function deletePages() {
		$pagesToDelete = [
			'My Account',
			'Checkout',
			'Sample Page',
		];

		foreach ( $pagesToDelete as $pageTitle ) {
			$page = Fns::getPageByTitle( $pageTitle );

			if ( $page ) {
				wp_delete_post( $page->ID, true );
			}
		}

		return new self();
	}

	/**
	 * Updates the 'Hello World!' blog post by making it a draft
	 *
	 * @return $this
	 */
	private static function draftPost() {
		// Update the Hello World! post by making it a draft.
		$helloWorld = Fns::getPageByTitle( 'Hello World!', 'post' );

		if ( $helloWorld ) {
			$helloWorldArgs = [
				'ID'          => $helloWorld->ID,
				'post_status' => 'draft',
			];

			// Update the post into the database.
			wp_update_post( $helloWorldArgs );
		}

		return new self();
	}
}
