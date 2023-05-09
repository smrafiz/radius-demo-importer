<?php
/**
 * Main bootstrap class.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter;

use RT\DemoImporter\Traits\SingletonTrait;
use RT\DemoImporter\Helpers\{
	Fns,
	Setup
};

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Main bootstrap class.
 */
final class Bootstrap {
	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * Nonce id
	 *
	 * @var string
	 */
	public $nonceId = '__rtdi_wpnonce';

	/**
	 * Nonce Text
	 *
	 * @var string
	 */
	public $nonceText = 'rtdi_nonce';

	/**
	 * Theme demo config.
	 *
	 * @var array
	 */
	public $config = [];

	/**
	 * Register plugin services.
	 *
	 * @return void
	 */
	public function registerServices() {

		// Plugin setup.
		$this->setup();

		// Init hooks.
		$this->initHooks();
	}

	/**
	 * Setup hooks (activation, deactivation)
	 *
	 * @return void
	 */
	public function setup() {
		register_activation_hook( RTDI_FILE, [ Setup::class, 'activation' ] );
		register_deactivation_hook( RTDI_FILE, [ Setup::class, 'deactivation' ] );
	}

	/**
	 * Init Hooks.
	 *
	 * @return void
	 */
	public function initHooks() {
		add_action( 'plugins_loaded', [ $this, 'onPluginsLoaded' ], - 1 );
		add_action( 'init', [ $this, 'register' ], 0 );
		add_action( 'after_setup_theme', [ $this, 'getDemoConfig' ] );
	}

	/**
	 * Get Theme demo config.
	 *
	 * @return void
	 */
	public function getDemoConfig() {
		$this->config = apply_filters( 'rtdi/importer/config', $this->config );
	}

	/**
	 * Actions on Plugins Loaded.
	 *
	 * @return void
	 */
	public function onPluginsLoaded() {
		do_action( 'rtdi/plugin/loaded' );
	}

	/**
	 * Init plugin.
	 *
	 * @return void
	 */
	public function register() {
		do_action( 'rtdi/importer/before/register' );

		// Define the locale.
		$this->setLocale();

		// Init services.
		Fns::initServices( $this->getServices() );

		do_action( 'rtdi/importer/after/register' );
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function setLocale() {
		load_plugin_textdomain(
			'radius-demo-importer',
			false,
			dirname( plugin_basename( RTDI_FILE ) ) . '/languages/'
		);
	}

	/**
	 * Stores all the core classes inside an array.
	 *
	 * @return array
	 */
	public function getServices() {
		$services = [];

		if ( is_admin() ) {
			$services[] = Controllers\Admin::class;
		}

		$services[] = Controllers\Importer::class;
		$services[] = Controllers\Scripts::class;
		$services[] = Controllers\Hooks\ActionHooks::class;
		$services[] = Controllers\Hooks\FilterHooks::class;

		return apply_filters( 'rtdi/importer/service/classes', $services );
	}

	/**
	 * Assets url generate with given assets file
	 *
	 * @param string $file File.
	 *
	 * @return string
	 */
	public function getAssetsUri( $file ) {
		$file = ltrim( $file, '/' );

		return trailingslashit( RTDI_URL . '/assets' ) . $file;
	}

	/**
	 * Plugin path.
	 *
	 * @return string
	 */
	public function pluginPath() {
		return untrailingslashit( plugin_dir_path( RTDI_FILE ) );
	}

	/**
	 * Supported Themes.
	 *
	 * @return mixed|null
	 */
	public function supportedThemes() {
		return apply_filters(
			'rtdi/importer/themes',
			[
				! empty( $this->config['themeSlug'] ) ? esc_html( $this->config['themeSlug'] ) : '',
			]
		);
	}

	/**
	 * Get active theme.
	 *
	 * @return false|mixed|null
	 */
	public function activeTheme() {
		return get_option( 'stylesheet' );
	}
}
