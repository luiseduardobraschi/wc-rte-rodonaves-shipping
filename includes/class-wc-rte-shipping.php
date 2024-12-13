<?php
/**
 * RTE Rodonaves Shipping Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WRRS_RTE_Shipping {
	/**
	 * Initialize the plugin public actions.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ), -1 );

		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins', ) ) ) ) {

			self::includes();

			add_filter( 'woocommerce_shipping_methods', array( __CLASS__, 'include_methods' ) );

		}
		else {
			add_action( 'admin_notices', array( __CLASS__, 'woocommerce_missing_notice' ) );
		}
	}

	private static function includes() {
		include_once dirname( __FILE__ ) . '/class-wc-rte-shipping-method.php';
		include_once dirname( __FILE__ ) . '/class-wc-rte-shipping-api.php';
		include_once dirname( __FILE__ ) . '/wc-rte-shipping-functions.php';
	}

	public static function include_methods( $methods ) {
		// Legacy method.
		$methods[ 'rte-rodonaves' ] = 'WRRS_RTE_Shipping_Method';
		return $methods;
	}

	public static function load_plugin_textdomain() {
		load_plugin_textdomain( 'wc-rte-rodonaves-shipping', false, dirname( plugin_basename( WRRS_RTE_SHIPPING_METHOD_FILE ) ) . '/languages/' );
	}
}