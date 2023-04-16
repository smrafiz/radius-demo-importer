<?php
/**
 * Singleton trait.
 *
 * @package RT\DemoImporter
 */

namespace RT\DemoImporter\Traits;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

trait SingletonTrait {
	/**
	 * The single instance of the class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Access the single instance of this class.
	 *
	 * @return object|SingletonTrait|null
	 */
	final public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Prevent cloning.
	 *
	 * @return void
	 */
	final public function __clone() {
	}

	/**
	 * Prevent serialization of the instance
	 *
	 * @return void
	 */
	public function __sleep() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'radius-demo-importer' ), '1.0.0' );
		die();
	}

	/**
	 * Prevent deserialization of the instance
	 *
	 * @return void
	 */
	final public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'radius-demo-importer' ), '1.0.0' );
		die();
	}
}
