<?php
/**
 * Plugin Name: Radius Demo Importer
 * Plugin URI: https://radiustheme.com/
 * Description: Import RadiusTheme official themes demo content, widgets and theme settings with just one click.
 * Author: RadiusTheme
 * Version: 1.0.0
 * Text Domain: radius-demo-importer
 * Domain Path: /languages
 * Author URI: https://radiustheme.com/
 *
 * @package RT\DemoImporter
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

define( 'RTDI_VERSION', '1.0.0' );
define( 'RTDI_FILE', __FILE__ );
define( 'RTDI_ACTIVE_FILE_NAME', plugin_basename( RTDI_FILE ) );
define( 'RTDI_PATH', plugin_dir_path( RTDI_FILE ) );
define( 'RTDI_URL', plugins_url( '', RTDI_FILE ) );

// Autoload plugin files.
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

/**
 * Demo Importer Instance.
 *
 * @return object
 */
function radiusDemoImporter() {
	return RT\DemoImporter\Bootstrap::instance();
}

/**
 * App Init.
 */
radiusDemoImporter()->registerServices();
