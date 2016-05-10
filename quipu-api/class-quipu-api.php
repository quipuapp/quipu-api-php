<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Quipu_Api_Connection' ) ) {
	include_once( 'class-quipu-api-connection.php' );
}

abstract class Quipu_Api {

	/**
	 * The request endpoint
	 *
	 * @var String
	 */
	private $endpoint = '';

	/**
	 * The query string
	 *
	 * @var string
	 */
	private $query = array();

	/**
	 * The request response
	 * @var array
	 */
	protected $response = null;

	/**
	 * @var Quipu_Api_Connection
	 */
	private $api_connection;

	/**
	 * @var Integrater
	 */
	protected $id = '';

	/**
	 * Quipu_Api constructor.
	 *
	 * @param string $api_key, $api_secret
	 */
	public function __construct( Quipu_Api_Connection $api_connection) {
		$this->api_connection = $api_connection;
	}

	/**
	 * Method to set id
	 *
	 * @param $id
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Get the id
	 *
	 * @return $id
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Method to set endpoint
	 *
	 * @param $endpoint
	 */
	protected function set_endpoint( $endpoint ) {
		$this->endpoint = $endpoint;
	}

	/**
	 * Get the endpoint
	 *
	 * @return String
	 */
	protected function get_endpoint() {
		return $this->endpoint;
	}

	/**
	 * @return string
	 */
	protected function get_query() {
		return $this->query;
	}


	/**
	 * @param string $query
	 */
	protected function set_query( $query ) {
		$this->query = $query;
	}

	/**
	 * Get response
	 *
	 * @return array
	 */
	public function get_response() {
		return $this->response;
	}

	/**
	 * Clear the response
	 *
	 * @return bool
	 */
	private function clear_response() {
		$this->response = null;

		return true;
	}

	public function create_request($post_data) {
		try {
			$this->response = $this->api_connection->post_request($this->endpoint, $post_data);

			if(isset($this->response['data']['id'])) {
				$this->set_id($this->response['data']['id']);
			}
		} catch (Exception $e) {
			throw $e;
		} 
	}

	public function get_request() {
		try {
			$this->response = $this->api_connection->get_request($this->endpoint);
		} catch (Exception $e) {
			throw $e;
		} 		
	}

	public function get_filter_request($query_string) {
		try {
			$this->response = $this->api_connection->get_request($this->endpoint.$query_string);

			if(isset($this->response['data'][0]['id'])) {
				$this->set_id($this->response['data'][0]['id']);
			}
		} catch (Exception $e) {
			throw $e;
		}		
	}

}
