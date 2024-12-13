<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_wrrs_get_city_id', 'wrrs_get_city_id_callback' );
add_action( 'woocommerce_checkout_create_order', 'wrrs_add_order_meta', 10, 2 );
add_action( 'woocommerce_after_order_itemmeta', 'wrrs_show_admin_shipping_infos', 10, 3 );
add_action( 'woocommerce_after_shipping_rate', 'wrrs_show_shipping_time_on_cart', 10, 2 );
add_action( 'woocommerce_order_details_after_customer_details', 'wrrs_show_client_additional_infos' );
add_action( 'wp_ajax_wrrs_get_shipping', 'wrrs_get_shipping_callback' );
add_action( 'wp_ajax_nopriv_wrrs_get_shipping', 'wrrs_get_shipping_callback' );
add_action( 'woocommerce_after_add_to_cart_form', 'wrrs_show_rte_shipping_in_product_page' );
add_shortcode( 'rte_shipping_in_product_page', 'wrrs_add_rte_shipping_shipping_simulator' );


function wrrs_get_city_id_callback() {
	if ( isset( $_GET ) ) {

		$shipping_method = wrrs_get_current_id();

		if ( sanitize_postcode( $shipping_method->zip_origin ) !== sanitize_postcode( $_GET[ 'data' ] ) ) {
			$shipping_method->zip_origin = $_GET[ 'data' ];
		}

		$response = $shipping_method->get_city_id();

		echo json_encode( $response );

		wp_die();
	}
}

function wrrs_add_order_meta( $order, $data ) {
	$shipping_method = reset( $order->get_items( 'shipping' ) )->get_method_id();

	if ( $shipping_method == 'rte-rodonaves' ) {
		$have_meta     = WC()->session->get( 'rte_extra_meta' );
		$delivery_time = WC()->session->get( 'delivery_time' );
		$weight        = WC()->cart->get_cart_contents_weight();

		$order->update_meta_data( '_rte_extra_meta', $have_meta );
		$order->update_meta_data( '_cart_weight', $weight );
		$order->update_meta_data( '_delivery_time', $delivery_time );
	}
}

function wrrs_show_shipping_time_on_cart( $method, $index ) {
	$delivery_time = WC()->session->get( 'delivery_time' );
	include_once( WRRS_RTE_SHIPPING_BASE_PATH . '/views/wc-rte-shipping-cart-page.php' );
}

function wrrs_show_admin_shipping_infos( $item_id, $item, $product ) {
	$order           = wc_get_order( $item[ 'order_id' ] );
	$shipping_method = reset( $order->get_items( 'shipping' ) )->get_method_id();

	if ( $shipping_method == 'rte-rodonaves' ) {
		$extra_meta    = $order->get_meta( '_rte_extra_meta' );
		$total_weight  = $order->get_meta( '_cart_weight' );
		$delivery_time = $order->get_meta( '_delivery_time' );

		if ( is_null( $extra_meta ) || $extra_meta != true ) {
			$total_weight  = __( 'No information.', 'wc-rte-rodonaves-shipping' );
			$delivery_time = __( 'No information.', 'wc-rte-rodonaves-shipping' );
		}
		else {
			$total_weight  = get_post_meta( $order->get_id(), '_cart_weight', true ) . get_option( 'woocommerce_weight_unit' );
			$delivery_time = get_post_meta( $order->get_id(), '_delivery_time', true ) . ' day(s)';
		}

		include( WRRS_RTE_SHIPPING_BASE_PATH . '/views/wc-rte-shipping-extra-meta-admin.php' );

	}
}

function wrrs_show_client_additional_infos( $order ) {
	$shipping_method = reset( $order->get_items( 'shipping' ) )->get_method_id();
	$delivery_time   = $order->get_meta( '_delivery_time' );

	if ( $shipping_method == 'rte-rodonaves' ) {

		include_once( WRRS_RTE_SHIPPING_BASE_PATH . '/views/wc-rte-shipping-user-order-infos.php' );
	}
}

function wrrs_add_rte_shipping_shipping_simulator() {
	global $product;

	$should_display_rte = $product instanceof \WC_Product && $product->is_virtual() === false;

	if ( $should_display_rte ) {
		if ( $product->is_type( 'variable' ) ) {
			wp_localize_script( 'rte-shipping-scripts', 'rteShippingProduct', array(
				'variable' => true,
			) );
			$product = wc_get_product( $product->get_children()[ 0 ] );
		}
		else {
			wp_localize_script( 'rte-shipping-scripts', 'rteShippingProduct', array(
				'variable' => false,
			) );
		}

		wp_localize_script( 'rte-shipping-scripts', 'rteShippingProductData', array(
			'data' => array(
				'weight'        => $product->get_weight(),
				'dimensions'    => array(
					'length' => $product->get_length(),
					'height' => $product->get_height(),
					'width'  => $product->get_width(),
				),
				'display_price' => $product->get_price(),
			)
		)
		);
	}

	include_once( WRRS_RTE_SHIPPING_BASE_PATH . '/views/wc-rte-shipping-product-page.php' );
}

function wrrs_get_shipping_callback() {
	if ( isset( $_POST ) ) {

		$shipping_method = wrrs_get_current_id();

		$data = wrrs_sanitize_data( $_POST[ 'data' ] );

		if ( ! is_null( $data ) ) {
			$response = $shipping_method->calculate_shipping( $data );
			echo json_encode( $response );
		}
		else {
			echo __( 'Wrong data sended.', 'wc-rte-rodonaves-shipping' );
		}

		wp_die();
	}
}

function wrrs_func_get_last_order_id() {
	if ( is_user_logged_in() ) {
		$customer_user_id = get_current_user_id();
		$last_order       = wc_get_customer_last_order( $customer_user_id );
		if ( ! empty( $last_order ) ) {
			$postcode = $last_order->get_billing_postcode();
			return $postcode;
		}
	}
	return;
}

function wrrs_show_rte_shipping_in_product_page() {
	$shipping_method = wrrs_get_current_id();
	if ( $shipping_method->show_simulator == 'yes' ) {
		wrrs_add_rte_shipping_shipping_simulator();
	}
}

function wrrs_get_current_id() {
	if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
		return;
	}

	$zones = WC_Shipping_Zones::get_zones();

	if ( ! is_array( $zones ) ) {
		return;
	}

	$shipping_methods = array_column( $zones, 'shipping_methods' );

	$flatten = array_merge( ...$shipping_methods );

	foreach ( $flatten as $key => $class ) {
		if ( $class->id == 'rte-rodonaves' ) {
			$instance_id = $class->get_instance_id();
			$class       = new WRRS_RTE_Shipping_Method( $instance_id );
			return $class;
		}
	}

	return;
}

function wrrs_sanitize_data( $data = array() ) {
	if ( is_array( $data ) ) {

		$model = array(
			'amountPackages' => '',
			'weight'         => '',
			'length'         => '',
			'height'         => '',
			'width'          => '',
			'postcode'       => '',
			'price'          => '',
			'product_page'   => '',
		);

		$comparator = array_diff_key( $data, $model );

		if ( count( $comparator ) === 0 ) {
			return array(
				'contents'      => array(
					'AmountPackages' => sanitize_text_field( $data[ 'amountPackages' ] ),
					'Weight'         => sanitize_text_field( $data[ 'weight' ] ),
					'Length'         => sanitize_text_field( $data[ 'length' ] ),
					'Height'         => sanitize_text_field( $data[ 'height' ] ),
					'Width'          => sanitize_text_field( $data[ 'width' ] )
				),
				'destination'   => array(
					'postcode' => sanitize_text_field( $data[ 'postcode' ] )
				),
				'cart_subtotal' => sanitize_text_field( $data[ 'price' ] ),
				'product_page'  => sanitize_text_field( $data[ 'product_page' ] )
			);
		}
	}

	return;
}
function sanitize_postcode( $string ) {
	return preg_replace( '([^0-9])', '', $string );
}