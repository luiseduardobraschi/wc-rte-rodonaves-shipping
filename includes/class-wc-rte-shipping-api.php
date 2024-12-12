<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WRRS_RTE_Shipping_API {
	/**
	 * API URL.
	 *
	 * @var string
	 */
	protected $get_token_url = 'https://01wapi.rte.com.br/token';
	protected $get_city_id_url = 'https://01wapi.rte.com.br/api/v1/busca-por-cep';
	protected $simulate_url = 'https://01wapi.rte.com.br/api/v1/simula-cotacao';

	/**
	 * API Security
	 *
	 * @var string
	 */
	protected $username = '';
	protected $password = '';
	protected $token = '';

	/**
	 * API constructor.
	 *
	 * @param string $username
	 * @param string $password
	 */

	public function __construct( $username, $password, $costumer_registration ) {
		$this->username              = $username;
		$this->password              = $password;
		$this->costumer_registration = $this->only_numbers( $costumer_registration );
		$this->token                 = get_option( 'WRRS_RTE_JWT' );
	}

	protected function only_numbers( $string ) {
		return preg_replace( '([^0-9])', '', $string );
	}

	protected function get_token() {
		$access_header = array( 'Content-Type' => 'application/x-www-form-urlencoded' );
		$access_body   = 'auth_type=DEV&grant_type=password&username=' . $this->username . '&password=' . $this->password;

		$response = $this->request_api( $this->get_token_url, 'POST', $access_header, $access_body );

		if ( array_key_exists( 'access_token', $response ) ) {
			update_option( 'WRRS_RTE_JWT', $response[ 'access_token' ] );
			$this->token = get_option( 'WRRS_RTE_JWT' );
		}
		else {
			echo 'Authorization has been denied for this request.';
		}
	}

	protected function refresh_token( $response ) {
		if ( array_key_exists( 'Message', $response ) && $response[ 'Message' ] == 'Authorization has been denied for this request.' ) {
			$this->get_token();
			return true;
		}
		elseif ( array_key_exists( 'Message', $response ) ) {
			echo esc_html( $response[ 'Message' ] );
			return true;
		}
		else {
			return false;
		}
	}

	protected function request_api( $url, $request_type, $header, $body ) {
		try {

			if ( $request_type === 'GET' ) {

				$args = array( 'headers' => $header );

				$response = wp_remote_get( $url, $args );

			}
			elseif ( $request_type === 'POST' ) {
				$args = array(
					'body'        => $body,
					'timeout'     => '30',
					'redirection' => '10',
					'httpversion' => '1.1',
					'blocking'    => true,
					'headers'     => $header,
					'cookies'     => array(),
				);

				$response = wp_remote_post( $url, $args );

			}
			else {
				return;
			}

			$response = wp_remote_retrieve_body( $response );
			$response = json_decode( $response, true );
			return $response;
		}
		catch ( Exception $err ) {
			return array( 'Message' => 'Exception Error:' . $err );
		}
	}

	public function get_simple_shipping_data( $package, $origin_data, $destination_data ) {
		if ( array_key_exists( 'product_page', $package ) ) {
			$product_page = true;
		}
		else {
			$product_page = false;
		}

		$data = array(
			'CustomerTaxIdRegistration' => $this->costumer_registration,
			'OriginZipCode'             => $origin_data[ 'postcode' ],
			'OriginCityId'              => $origin_data[ 'id' ],
			'DestinationZipCode'        => $destination_data[ 'postcode' ],
			'DestinationCityId'         => $destination_data[ 'id' ],
			'EletronicInvoiceValue'     => $package[ 'cart_subtotal' ],
			'TotalWeight'               => ( $product_page ? $package[ 'contents' ][ 'Weight' ] : WC()->cart->cart_contents_weight ),
			'Packs'                     => ( $product_page ? array( $package[ 'contents' ] ) : $this->get_package_data( $package ) ),
		);

		return $data;
	}

	public function shipping_simulation( $package = array(), $origin_data ) {
		$header = array(
			'Accept'        => 'application/json',
			'Authorization' => 'Bearer ' . $this->token,
			'Content-Type'  => 'text/json'
		);

		if ( ! $package[ 'destination' ][ 'postcode' ] ) {
			return;
		}

		$destination_data = $this->get_postcode_data( $package[ 'destination' ][ 'postcode' ] );

		if ( is_null( $destination_data ) ) {
			return 'Postcode is not valid.';
		}

		$body = $this->get_simple_shipping_data( $package, $origin_data, $destination_data );
		$body = json_encode( $body );

		$response = $this->request_api( $this->simulate_url, 'POST', $header, $body );

		if ( $this->refresh_token( $response ) ) {
			$header   = array(
				'Accept'        => 'application/json',
				'Authorization' => 'Bearer ' . $this->token,
				'Content-Type'  => 'text/json'
			);
			$response = $this->request_api( $this->simulate_url, 'POST', $header, $body );
		}

		return $response;
	}

	public function get_postcode_data( $zip_code ) {
		if ( $zip_code == '' ) {
			return;
		}
		$header = array(
			'Accept'        => 'application/json',
			'Authorization' => 'Bearer ' . $this->token,
		);

		$url = $this->get_city_id_url . '?zipCode=' . $this->only_numbers( $zip_code );

		$response = $this->request_api( $url, 'GET', $header, '' );

		if ( $this->refresh_token( $response ) ) {
			$header   = array(
				'Accept'        => 'application/json',
				'Authorization' => 'Bearer ' . $this->token,
			);
			$response = $this->request_api( $url, 'GET', $header, '' );
		}

		if ( array_key_exists( '0', $response ) && $response[ '0' ][ 'Message' ] == 'CEP não encontrado' ) {
			return $response[ '0' ][ 'Message' ];
		}

		$data = array(
			'id'       => $response[ 'CityId' ],
			'name'     => $response[ 'CityDescription' ],
			'state'    => $response[ 'UnitFederation' ][ 'Description' ],
			'district' => $response[ 'District' ],
			'street'   => $response[ 'Street' ],
			'postcode' => $response[ 'ZipCode' ]
		);

		return $data;
	}

	function get_package_data( $package ) {
		$pack = array();

		foreach ( $package[ 'contents' ] as $item_id => $value ) {
			$product = $value[ 'data' ];
			$qty     = $value[ 'quantity' ];

			if ( $qty > 0 && $product->needs_shipping() ) {
				$product_data = array(
					'AmountPackages' => (int)$qty,
					'Weight'         => (float)$product->get_weight(),
					'Length'         => (float)$product->get_length(),
					'Height'         => (float)$product->get_height(),
					'Width'          => (float)$product->get_width()
				);

				array_push( $pack, $product_data );
			}
		}

		return $pack;
	}
}