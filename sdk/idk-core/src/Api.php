<?php

namespace Ingk;

/**
 * Class Api
 *
 * @package Ingk
 */
class Api {

	/**
	 * @const string
	 */
	const ENVIRONMENT_PRODUCTION = 'production';

	// region keys for urls

	/**
	 * @const string
	 */
	const CREATE_INVOICE = 'create_invoice';

	/**
	 * @const string
	 */
	const GET_INVOICE = 'get_invoice';

	// endregion

	/**
	 * @const string
	 */
	const RM_POST = 'POST';

	/**
	 * @var array
	 */
	private static $serviceUrls = [
		self::ENVIRONMENT_PRODUCTION => 'https://ksiegowosc.ing.pl',
	];

	/**
	 * @var array
	 */
	private static $endpoints = [
		self::CREATE_INVOICE => '/v2/integrations/paymento/orders',
		self::GET_INVOICE    => '/modules-documents/preview',
	];

	/**
	 * @var string
	 */
	private $apiKey;

	/**
	 * @var string
	 */
	private $environment;

	/**
	 * Api constructor.
	 *
	 * @param string $apiKey
	 * @param string $environment
	 */
	public function __construct(
		$apiKey,
		$environment = ''
	) {

		$this->apiKey = $apiKey;

		$this->environment = $environment
			?: self::ENVIRONMENT_PRODUCTION;
	}

	/**
	 * @param string $body
	 *
	 * @return array
	 */
	public function createInvoice( $body ) {

		return $this->call(
			$this->getEndpointUrl( self::CREATE_INVOICE ),
			self::RM_POST,
			$body
		);
	}

	/**
	 * @param string $url
	 * @param string $methodRequest
	 * @param string $body
	 *
	 * @return array
	 */
	private function call( $url, $methodRequest, $body = '' ) {


		$curl = curl_init( $url );
		curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, $methodRequest );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $body );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 20 );
		curl_setopt( $curl, CURLOPT_TIMEOUT, 20 );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Paymento-Api-Token: ' . $this->apiKey,
		] );

		$resultCurl = json_decode( curl_exec( $curl ), true );

		$httpCode = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

		if ( ( $httpCode !== 201 ) || ! $resultCurl ) {

			return [
				'success' => false,
				'data'    => [
					'httpCode' => $httpCode,
					'error'    => curl_error( $curl ),
					'body'     => $resultCurl,
				],
			];
		}

		return [
			'success' => true,
			'data'    => $resultCurl,
		];
	}


	/**
	 * @param string $endpoint
	 *
	 * @return string
	 */
	private function getEndpointUrl( $endpoint, $id = '' ) {

		if ( empty( self::$endpoints[ $endpoint ] ) ) {
			return '';
		}

		$baseUrl = self::getServiceUrl();

		if ( ! $baseUrl ) {
			return '';
		}

		switch ( $endpoint ) {
			case self::CREATE_INVOICE:
				return $baseUrl . self::$endpoints[ $endpoint ];
			default:
				return '';
		}
	}

	/**
	 * @param string $id
	 *
	 * @return string
	 */
	public function getPreviewInvoice( $id = '' ) {

		$baseUrl = self::getServiceUrl();

		if ( ! $baseUrl ) {
			return '';
		}

		return $baseUrl . self::$endpoints[ self::GET_INVOICE ] . '/' . $id . '/0';
	}

	/**
	 * @return string
	 */
	private function getServiceUrl() {

		if ( isset( self::$serviceUrls[ $this->environment ] ) ) {
			return self::$serviceUrls[ $this->environment ];
		}

		return '';
	}
}
