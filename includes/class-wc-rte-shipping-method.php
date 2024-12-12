<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WRRS_RTE_Shipping_Method extends WC_Shipping_Method {
	/**
	 * Constructor for your shipping class
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'rte-rodonaves';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'RTE Rodonaves Shipping', 'wc-rte-rodonaves-shipping' );
		$this->method_description = __( 'RTE Rodonaves shipping integration', 'wc-rte-rodonaves-shipping' );

		$this->title = isset( $this->settings[ 'title' ] ) ? $this->settings[ 'title' ] :
			__( 'RTE Rodonaves Shipping', 'wc-rte-rodonaves-shipping' );

		// Load the settings API
		$this->init_form_fields();

		// Define user set variables.
		$this->enabled               = $this->get_option( 'enabled' );
		$this->username              = $this->get_option( 'username' );
		$this->password              = $this->get_option( 'password' );
		$this->costumer_registration = $this->get_option( 'costumer_registration' );
		$this->zip_origin            = $this->get_option( 'zip_origin' );
		$this->city_id               = $this->get_option( 'city_id' );
		$this->shipping_class_id     = (int)$this->get_option( 'shipping_class_id', '-1' );
		$this->additional_time       = (int)$this->get_option( 'additional_time' );
		$this->fee_type              = $this->get_option( 'fee_type' );
		$this->fee                   = (float)$this->get_option( 'fee' );
		$this->debug                 = $this->get_option( 'debug' );
		$this->show_simulator        = $this->get_option( 'shipping_simulator' );

		// Active logs.
		if ( 'yes' === $this->debug ) {
			$this->log = new WC_Logger();
		}

		// Method variables.
		$this->availability = 'specific';
		$this->countries    = array( 'BR' );

		$this->supports = array(
			'shipping-zones',
			'instance-settings',
		);

		// Save settings in admin if you have any defined
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Get shipping classes options.
	 *
	 * @return array
	 */
	protected function get_shipping_classes_options() {
		$shipping_classes = WC()->shipping->get_shipping_classes();
		$options          = array(
			'-1' => __( 'Any Shipping Class', 'wc-rte-rodonaves-shipping' ),
			'0'  => __( 'No Shipping Class', 'wc-rte-rodonaves-shipping' ),
		);

		if ( ! empty( $shipping_classes ) ) {
			$options += wp_list_pluck( $shipping_classes, 'name', 'term_id' );
		}

		return $options;
	}

	/**
	 * Define settings field for this shipping
	 * @access public
	 * @return void 
	 */
	function init_form_fields() {
		$this->instance_form_fields = array(
			'enabled'               => array(
				'title'       => __( 'Enable', 'wc-rte-rodonaves-shipping' ),
				'type'        => 'checkbox',
				'description' => __( 'Enable this shipping.', 'wc-rte-rodonaves-shipping' ),
				'default'     => 'yes'
			),
			'username'              => array(
				'title'       => __( 'Login', 'wc-rte-rodonaves-shipping' ),
				'type'        => 'text',
				'description' => __( 'API RTE Rodonaves login', 'wc-rte-rodonaves-shipping' ),
				'desc_tip'    => true,
			),
			'password'              => array(
				'title'       => __( 'Password', 'wc-rte-rodonaves-shipping' ),
				'type'        => 'password',
				'description' => __( 'API RTE Rodonaves password.', 'wc-rte-rodonaves-shipping' ),
				'desc_tip'    => true,
			),
			'costumer_registration' => array(
				'title'       => __( 'Registered CPF or CNPJ', 'wc-rte-rodonaves-shipping' ),
				'type'        => 'text',
				'description' => __( 'API RTE Rodonaves registered CPF or CNPJ.', 'wc-rte-rodonaves-shipping' ),
			),
			'zip_origin'            => array(
				'title'       => __( 'Origin postcode', 'wc-rte-rodonaves-shipping' ),
				'type'        => 'text',
				'description' => __( 'Postcode from where the requests are sent.', 'wc-rte-rodonaves-shipping' ),
				'placeholder' => '00000-000',
			),
			'city_id'               => array(
				'title'       => __( 'City ID code', 'wc-rte-rodonaves-shipping' ),
				'type'        => 'text',
				'description' => sprintf( '%1$s<br><strong>%2$s</strong><br>%3$s',
					__( 'City ID is necessary to request API.', 'wc-rte-rodonaves-shipping' ),
					__( 'Remember to update City ID if you change postcode.', 'wc-rte-rodonaves-shipping' ),
					__( 'If you don&lsquo;t know what is a ID, fill all fields above and click here: ', 'wc-rte-rodonaves-shipping' ) ) . $this->get_city_id_button(),
				'placeholder' => '0000',
			),
			'shipping_simulator'    => array(
				'title'       => __( 'Shipping Simulator', 'wc-rte-rodonaves-shipping' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable simulator', 'wc-rte-rodonaves-shipping' ),
				'description' => sprintf( '%1$s<br>%2$s<br><strong>%3$s</strong>',
					__( 'Adds shipping simulator automatically on the product page.', 'wc-rte-rodonaves-shipping' ),
					__( 'If necessary, you can disable and use the shortcode below:', 'wc-rte-rodonaves-shipping' ),
					'[rte_shipping_in_product_page]' ),
				'default'     => 'yes',
			),
			'shipping_class_id'     => array(
				'title'       => __( 'Shipping Class', 'wc-rte-rodonaves-shipping' ),
				'type'        => 'select',
				'description' => __( 'If necessary, select a shipping class to apply this method.', 'wc-rte-rodonaves-shipping' ),
				'desc_tip'    => true,
				'default'     => '',
				'class'       => 'wc-enhanced-select',
				'options'     => $this->get_shipping_classes_options(),
			),
			'additional_time'       => array(
				'title'       => __( 'Additional Days', 'wc-rte-rodonaves-shipping' ),
				'type'        => 'text',
				'description' => __( 'Additional days to the estimated delivery.', 'wc-rte-rodonaves-shipping' ),
				'default'     => '1',
				'placeholder' => '1',
			),
			'fee_type'              => array(
				'title'       => __( 'Handling Fee Type', 'wc-rte-rodonaves-shipping' ),
				'type'        => 'select',
				'description' => __( 'Enter handling fee type, if is percent or solid value.', 'wc-rte-rodonaves-shipping' ),
				'default'     => 'solid',
				'options'     => array( 'solid' => __( 'solid', 'wc-rte-rodonaves-shipping' ),
					'percent' => __( 'percent', 'wc-rte-rodonaves-shipping' ) ),
				'desc_tip'    => true
			),
			'fee'                   => array(
				'title'       => __( 'Handling Fee', 'wc-rte-rodonaves-shipping' ),
				'type'        => 'text',
				'description' => __( 'Enter an value, e.g. 2.50. Leave blank to disable.', 'wc-rte-rodonaves-shipping' ),
				'default'     => '0',
				'placeholder' => '0.00',
			),
			'debug'                 => array(
				'title'       => __( 'Debug Log', 'wc-rte-rodonaves-shipping' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'wc-rte-rodonaves-shipping' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Log %s events, such as WebServices requests.', 'wc-rte-rodonaves-shipping' ),
					$this->method_title ) . $this->get_log_link(),
			),
		);
	}

	protected function get_city_id_button() {
		return '<button id="wc-get-city-id">' . __( 'Get City ID', 'wc-rte-rodonaves-shipping' ) . '</button>' .
			'<div id="wc-get-city-id-response"></div>';
	}

	/**
	 * Get log.
	 *
	 * @return string
	 */
	protected function get_log_link() {
		return ' <a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' .
			esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.log' ) ) . '">' .
			__( 'View logs.', 'wc-rte-rodonaves-shipping' ) . '</a>';
	}

	public function get_city_id() {
		$api     = new WRRS_RTE_Shipping_API( $this->username, $this->password, $this->costumer_registration );
		$city_id = $api->get_postcode_data( $this->zip_origin );
		return $city_id[ 'id' ];
	}

	/**
	 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
	 *
	 * @access public
	 * @param mixed $package
	 */
	public function calculate_shipping( $package = array() ) {
		if ( $this->username === ''
			|| $this->password === ''
			|| $this->costumer_registration === ''
			|| $this->zip_origin === ''
			|| $this->city_id === '' ) {
			echo __( 'Some necessary field in the plugin is not configured.', 'wc-rte-rodonaves-shipping' );
			return;
		}
		if ( ! $package[ 'destination' ][ 'postcode' ] ) {
			return;
		}

		if ( get_option( 'woocommerce_weight_unit' ) === 'g' ) {
			$package[ 'contents' ][ 'Weight' ] = ( $package[ 'contents' ][ 'Weight' ] / 1000 );
		}

		$api = new WRRS_RTE_Shipping_API( $this->username, $this->password, $this->costumer_registration );

		$city_data = array(
			'postcode' => $this->zip_origin,
			'id'       => $this->city_id
		);

		$fee_tax       = 0;
		$shipping_data = $api->shipping_simulation( $package, $city_data );

		if ( is_null( $shipping_data ) ) {
			return 'Error';
		}
		else if ( array_key_exists( '0', $shipping_data ) ) {
			return $shipping_data[ '0' ][ 'Message' ];
		}

		if ( ! is_numeric( $this->fee ) ) {
			$this->fee = 0;
		}

		if ( ! is_numeric( $this->additional_time ) ) {
			$this->additional_time = 1;
		}

		if ( $this->fee_type === 'solid' ) {
			$fee_tax = $this->fee + $shipping_data[ 'Value' ];
		}
		else {
			$fee_tax = $shipping_data[ 'Value' ] + ( ( $shipping_data[ 'Value' ] * $this->fee ) / 100 );
		}

		if ( ! array_key_exists( 'product_page', $package ) ) {
			$rate = array(
				'label'    => $this->title,
				'cost'     => $fee_tax,
				'calc_tax' => 'per_item'
			);

			WC()->session->set( 'rte_extra_meta', true );
			WC()->session->set( 'delivery_time', $shipping_data[ 'DeliveryTime' ] + $this->additional_time );

			// Register the rate
			$this->add_rate( $rate );
		}
		else {
			$shipping_data[ 'Value' ]        = number_format( $fee_tax, 2, ',', '' );
			$shipping_data[ 'DeliveryTime' ] = $shipping_data[ 'DeliveryTime' ] + $this->additional_time;
			return $shipping_data;
		}
	}
}