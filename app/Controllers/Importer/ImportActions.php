<?php
/**
 * Import Actions Class.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Controllers\Importer;

use RT\DemoImporter\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Action Hooks Class.
 */
class ImportActions {
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
	 * Executes operations after import.
	 *
	 * @param object $obj Reference object.
	 *
	 * @return void
	 */
	public static function afterImportActions( $obj ) {
		self::assignPages( $obj )
			->replaceUrls( $obj )
			->assignWooPages()
			->setElementorActiveKit()
			->setElementorSettings( $obj )
			->updatePermalinks()
			->rewriteFlag();
		// ->elementorCategoryFix()
		// ->elementorfix()
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
	 * @return void
	 */
	private static function draftPost() {
		$helloWorld = Fns::getPageByTitle( 'Hello World!', 'post' );

		if ( $helloWorld ) {
			$helloWorldArgs = [
				'ID'          => $helloWorld->ID,
				'post_status' => 'draft',
			];

			wp_update_post( $helloWorldArgs );
		}
	}

	/**
	 * Setting up pages.
	 *
	 * @param object $obj Reference object.
	 *
	 * @return static
	 */
	public static function assignPages( $obj ) {
		$homeSlug         = $obj->demoSlug;
		$blogSlug         = '';

		if ( ! empty( $obj->config['blogSlug'] ) || ! empty( $obj->config['demoData'][ $obj->demoSlug ]['blogSlug'] ) ) {
			$blogSlug = $obj->multiple ? $obj->config['demoData'][ $obj->demoSlug ]['blogSlug'] : $obj->config['blogSlug'];
		}

		// Setting up front page.
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

		// Setting up blog page.
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

		return new static();
	}

	/**
	 * Replaces URL.
	 *
	 * @param object $obj Reference object.
	 *
	 * @return static
	 */
	public static function replaceUrls( $obj ) {
		global $wpdb;

		if ( ! empty( $obj->config['replaceEmail'] ) ) {
			// Commenter email.
			$commenter_email     = esc_sql( $obj->config['replaceEmail'] );
			$commenter_new_email = esc_sql( get_bloginfo( 'admin_email' ) );
			$commenter_new_url   = esc_sql( home_url() );

			$query = $wpdb->prepare(
				"
			UPDATE $wpdb->comments
			SET comment_author_email = %s, comment_author_url = %s
			WHERE comment_author_email = %s",
				$commenter_new_email,
				$commenter_new_url,
				$commenter_email
			);

			$wpdb->query( $query );
		}

		if ( empty( $obj->config['urlToReplace'] ) ) {
			return new static();
		}

		$urls = [
			'slash'   => [
				'old' => trailingslashit( $obj->config['urlToReplace'] ),
				'new' => home_url( '/' ),
			],
			'unslash' => [
				'old' => untrailingslashit( $obj->config['urlToReplace'] ),
				'new' => home_url(),
			],
		];

		foreach ( $urls as $url ) {
			$oldUrl = esc_sql( $url['old'] );
			$newUrl = esc_sql( $url['new'] );

			// Table names and columns to update.
			$tables = [
				$wpdb->prefix . 'posts'       => [ 'post_content' ],
				$wpdb->prefix . 'postmeta'    => [ 'meta_value' ],
				$wpdb->prefix . 'options'     => [ 'option_value' ],
				$wpdb->prefix . 'comments'    => [ 'comment_content' ],
				$wpdb->prefix . 'commentmeta' => [ 'meta_value' ],
			];

			// Search and replace URLs in all tables and columns.
			foreach ( $tables as $table => $columns ) {
				foreach ( $columns as $column ) {
					$sql = $wpdb->prepare( "UPDATE $table SET $column = replace($column, %s, %s)", $oldUrl, $newUrl );

					$wpdb->query( $sql );
				}
			}

			// Search and replace URLs in Elementor data (postmeta).
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->postmeta} " .
					'SET `meta_value` = REPLACE(`meta_value`, %s, %s) ' .
					"WHERE `meta_key` = '_elementor_data' AND `meta_value` LIKE '[%%' ;",
					str_replace( '/', '\/', $oldUrl ),
					str_replace( '/', '\/', $newUrl )
				)
			);

			// Replace GUID.
			$query = $wpdb->prepare(
				"
                UPDATE $wpdb->posts
                SET guid = REPLACE(guid, %s, %s)
                WHERE guid LIKE %s",
				$oldUrl,
				$newUrl,
				$wpdb->esc_like( $oldUrl ) . '%'
			);
			$wpdb->query( $query );
		}

		return new static();
	}

	/**
	 * Assigns WooCommerce pages.
	 *
	 * @return static
	 */
	public static function assignWooPages() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return new static();
		}

		global $wpdb;

		$wcPages = [
			'shop'      => [
				'name'  => 'shop',
				'title' => 'Shop',
			],
			'cart'      => [
				'name'  => 'cart',
				'title' => 'Cart',
			],
			'checkout'  => [
				'name'  => 'checkout',
				'title' => 'Checkout',
			],
			'myaccount' => [
				'name'  => 'my-account',
				'title' => 'My Account',
			],
		];

		// Set WC pages properly.
		foreach ( $wcPages as $key => $wcPage ) {

			// Get the ID of every page with matching name or title.
			$pageIds = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID FROM $wpdb->posts WHERE (post_name = %s OR post_title = %s) AND post_type = 'page' AND post_status = 'publish'",
					$wcPage['name'],
					$wcPage['title']
				)
			);

			// Create page if $pageIds returns null.
			if ( empty( $pageIds ) ) {
				$pageId = wp_insert_post(
					[
						'post_title'   => $wcPage['title'],
						'post_name'    => $wcPage['name'],
						'post_content' => '',
						'post_status'  => 'publish',
						'post_type'    => 'page',
					]
				);

				if ( $pageId ) {
					update_option( 'woocommerce_' . $key . '_page_id', $pageId );
				}
			} else {
				$pageId    = 0;
				$deleteIds = [];

				// Retrieve page with greater id and delete others.
				if ( count( $pageIds ) > 1 ) {
					foreach ( $pageIds as $page ) {
						if ( $page->ID > $pageId ) {
							if ( $pageId ) {
								$deleteIds[] = $pageId;
							}

							$pageId = $page->ID;
						} else {
							$deleteIds[] = $page->ID;
						}
					}
				} else {
					$pageId = $pageIds[0]->ID;
				}

				// Delete posts.
				foreach ( $deleteIds as $delete_id ) {
					wp_delete_post( $delete_id, true );
				}

				// Update WC page.
				if ( $pageId > 0 ) {
					wp_update_post(
						[
							'ID'        => $pageId,
							'post_name' => sanitize_title( $wcPage['name'] ),
						]
					);
					update_option( 'woocommerce_' . $key . '_page_id', $pageId );
				}
			}
		}

		// We no longer need WC setup wizard redirect.
		delete_transient( '_wc_activation_redirect' );

		return new static();
	}

	/**
	 * Sets the active Elementor kit.
	 *
	 * @return static
	 */
	public static function setElementorActiveKit() {
		global $wpdb;

		$pageIds = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE (post_name = %s OR post_title = %s) AND post_type = 'elementor_library' AND post_status = 'publish'",
				'default-kit',
				'Default Kit'
			)
		);

		if ( ! is_null( $pageIds ) ) {
			$pageId    = 0;
			$deleteIds = [];

			// Retrieve page with greater id and delete others.
			if ( count( $pageIds ) > 1 ) {
				foreach ( $pageIds as $page ) {
					if ( $page->ID > $pageId ) {
						if ( $pageId ) {
							$deleteIds[] = $pageId;
						}

						$pageId = $page->ID;
					} else {
						$deleteIds[] = $page->ID;
					}
				}
			} else {
				$pageId = $pageIds[0]->ID;
			}

			// Update `elementor_active_kit` page.
			if ( $pageId > 0 ) {
				wp_update_post(
					[
						'ID'        => $pageId,
						'post_name' => sanitize_title( 'Default Kit' ),
					]
				);
				update_option( 'elementor_active_kit', $pageId );
			}
		}

		return new static();
	}

	/**
	 * Sets the Elementor default settings.
	 *
	 * @param object $obj Reference object.
	 *
	 * @return static
	 */
	public function setElementorSettings( $obj ) {
		$defaultPostTypes = [ 'page', 'post' ];
		$postTypesSupport = ! empty( $obj['elementorCptSupport'] ) ? array_merge( $defaultPostTypes, $obj['elementorCptSupport'] ) : $defaultPostTypes;

		if ( ! empty( $postTypesSupport ) ) {
			update_option( 'elementor_cpt_support', $postTypesSupport );
		}

		// Other Settings.
		update_option( 'elementor_disable_color_schemes', 'yes' );
		update_option( 'elementor_disable_typography_schemes', 'yes' );
		update_option( 'elementor_experiment-e_swiper_latest', 'inactive' );
		update_option( 'elementor_unfiltered_files_upload', '1' );

		return new static();
	}

	/**
	 * Sets rewrite flag.
	 *
	 * @return static
	 */
	public static function rewriteFlag() {
		$theme  = str_replace( '-', '_', radiusDemoImporter()->activeTheme() );
		$option = $theme . '_rtdi_rewrite_flash';

		update_option( $option, 'true' );
		update_option( 'rtdi_import_success', 'true' );

		return new static();
	}

	/**
	 * Updates the permalink structure to "/%postname%/".
	 *
	 * @return static
	 */
	public static function updatePermalinks() {
		update_option( 'permalink_structure', '/%postname%/' );
		flush_rewrite_rules();

		return new static();
	}
}
