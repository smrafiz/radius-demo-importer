<?php
/**
 * Plugin Deactivation Notice Class.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Controllers\Admin;

use RT\DemoImporter\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Plugin Deactivation Notice Class.
 */
class DeactivationNotice {
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
		add_action( 'admin_notices', [ $this, 'noticeMarkup' ], 0 );
		add_action( 'admin_init', [ $this, 'deactivatePlugin' ], 0 );
		add_action( 'admin_init', [ $this, 'ignoreNotice' ], 0 );
	}

	/**
	 * HTML markup of notice.
	 *
	 * @return void
	 */
	public function noticeMarkup() {
		$importSuccess          = get_option( 'rtdi_import_success' );
		$ignoreDeactivateNotice = get_option( 'rtdi_plugin_deactivate_notice' );

		if ( ! $importSuccess || ! current_user_can( 'deactivate_plugin' ) || ( $ignoreDeactivateNotice && current_user_can( 'deactivate_plugin' ) ) ) {
			return;
		}
		?>
		<div class="notice notice-success rtdi-notice plugin-deactivate-notice is-dismissible"
			 style="position:relative;">
			<p>
				<?php
				_e(
					'It seems you\'ve imported the theme demo data successfully. So, the purpose of <b>Radius Demo Importer</b> plugin is fulfilled and it has no more use. <br />If you\'re satisfied with imported theme demo data, you can safely deactivate it by clicking below \'Deactivate\' button.',
					'radius-demo-importer'
				);
				?>
			</p>

			<p class="links">
				<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'deactivate-radius-demo-importer', 'true' ), 'deactivate_rtdi_plugin', '_deactivate_rtdi_plugin_nonce' ) ); ?>"
				   class="btn button-primary">
					<span><?php esc_html_e( 'Deactivate Plugin', 'radius-demo-importer' ); ?></span>
				</a>
				<a class="btn button-secondary" href="?nag_rtdi_plugin_deactivate_notice=0">Dismiss This
					Notice</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Plugin deactivation.
	 *
	 * @return void
	 */
	public function deactivatePlugin() {
		// Deactivate the plugin.
		if ( isset( $_GET['deactivate-radius-demo-importer'] ) && isset( $_GET['_deactivate_rtdi_plugin_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_GET['_deactivate_rtdi_plugin_nonce'], 'deactivate_rtdi_plugin' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'radius-demo-importer' ) );
			}

			// Get the plugin.
			$plugin = RTDI_ACTIVE_FILE_NAME;

			if ( is_plugin_active( $plugin ) ) {
				deactivate_plugins( $plugin );
			}

			// Redirect to main dashboard page.
			wp_safe_redirect( admin_url( 'plugins.php' ) );
		}
	}

	/**
	 * Remove the plugin deactivate notice permanently.
	 */
	public function ignoreNotice() {
		/* If user clicks to ignore the notice, add that to the options table. */
		if ( isset( $_GET['nag_rtdi_plugin_deactivate_notice'] ) && '0' == $_GET['nag_rtdi_plugin_deactivate_notice'] ) {
			update_option( 'rtdi_plugin_deactivate_notice', 'true' );
		}
	}
}
