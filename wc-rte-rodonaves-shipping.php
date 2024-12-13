<?php
/**
 * Plugin Name: 	RTE Rodonaves Shipping
 * Plugin URI: 		https://wordpress.org/plugins/wc-rte-rodonaves-shipping/
 * Description: 	Plugin add RTE Rodonaves Shippging to WooCommerce.
 * Version: 		0.2.0
 * Author: 			Paulo Cezario
 * License:			GPLv2 or later
 * Text Domain: 	wc-rte-rodonaves-shipping
 * Domain Path:  	/languages
 * Author URI: 		https://github.com/phscezario/
 *
 * Este plugin adiciona o sistema de frete RTE Rodonaves a sua loja virtual Wordpress com WooCommerce.
 **/

/** If this file is called directly, abort. */
defined( 'ABSPATH' ) || die();

define( 'WRRS_RTE_SHIPPING_METHOD_FILE', __FILE__ );
define( 'WRRS_RTE_SHIPPING_BASE_PATH', __DIR__ );

/** Define token to access */
define( 'WRRS_RTE_JWT', '' );

/*
 * Check if Class exist
 */
if ( ! class_exists( 'WRRS_RTE_Shipping' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-wc-rte-shipping.php';
	add_action( 'plugins_loaded', array( 'WRRS_RTE_Shipping', 'init' ) );
	add_action( 'wp_enqueue_scripts', 'wrrs_add_assets' );
	add_action( 'admin_enqueue_scripts', 'wrrs_add_admin_assets' );
	add_action( 'woocommerce_enqueue_styles', 'wrrs_add_user_assets' );
	add_action( 'woocommerce_after_settings_shipping', 'wrrs_admin_get_city_id' );
}

function wrrs_add_assets() {
	if ( is_product() ) {
		wp_enqueue_style( 'rte-shipping-styles', plugin_dir_url( __FILE__ ) . 'assets/css/rte-shipping-styles.css' );
		wp_enqueue_script( 'rte-shipping-scripts', plugin_dir_url( __FILE__ ) . 'assets/js/rte-shipping-scripts.js', array( 'jquery' ), '', true );
		wp_localize_script( 'rte-shipping-scripts', 'rteShippingData', array(
			'url'    => admin_url( 'admin-ajax.php' ),
			'action' => 'wrrs_get_shipping',
			'text'   => array(
				'estimated' => __( 'Estimated Time', 'wc-rte-rodonaves-shipping' ),
				'days'      => __( 'working day(s)', 'wc-rte-rodonaves-shipping' ),
				'error'     => __( 'Entry a correct postcode.', 'wc-rte-rodonaves-shipping' ),
				'postcode'  => __( 'Postcode', 'wc-rte-rodonaves-shipping' ),
				'adversity' => __( 'Maybe you need to calculate shipping again after change product variation or quantity.',
					'wc-rte-rodonaves-shipping' )
			)
		) );
	}
}

function wrrs_add_admin_assets() {
	wp_enqueue_style( 'rte-shipping-admin-styles', plugin_dir_url( __FILE__ ) . 'assets/css/rte-shipping-admin-styles.css' );
}

function wrrs_add_user_assets() {
	wp_enqueue_style( 'rte-shipping-admin-styles', plugin_dir_url( __FILE__ ) . 'assets/css/rte-shipping-user-styles.css' );
}

function wrrs_admin_get_city_id() {
	wp_enqueue_script( 'rte-shipping-city-id-script', plugin_dir_url( __FILE__ ) . 'assets/js/rte-shipping-city-id.js', array( 'jquery' ), '', true );
	wp_localize_script( 'rte-shipping-city-id-script', 'rteShippingCityData', array(
		'url'    => admin_url( 'admin-ajax.php' ),
		'action' => 'wrrs_get_city_id',
		'error'  => __( 'You need fill, Login, Password, Registered CPF or CNPJ and Origin postcode correctly, for it work', 'wc-rte-rodonaves-shipping' ), )
	);
}