<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

// Singleton API connection class
class Quipu_Api_Connection {

	/**
	 * @var String API URL
	 */
	const API_URL = 'https://getquipu.com/';

	/**
	 * @var String API URL
	 */
	const AUTH_URL = 'oauth/token';

	/**
	 * @var string
	 */
	private $api_key;

	/**
	 * @var string
	 */
	private $api_secret;

	/**
	 * @var string
	 */
	private $access_token;

	/**
	 * @var resource
	 */
	private $curl;

	/**
	 * @var time
	 */
	private $token_expires = 0;

	/**
	 * @var string
	 */
	private $error_msg;

	/**
	 * @var Quipu_Api_Connection
	 */
	private static $_instance; //The single instance


	/*
	 * Get an instance of the Database
	 * @return Instance
	*/
	public static function get_instance($api_key, $api_secret) {

		// If no instance then make one, else if the API key and secret are not the same i.e. new account/connection
		if( (!self::$_instance) ) { 
			self::$_instance = new self($api_key, $api_secret);
		} elseif( !(self::$_instance->is_key_match($api_key, $api_secret)) ) { 
			self::$_instance = new self($api_key, $api_secret);
		}

		return self::$_instance;
	}

	/**
	 *  Check is the passed key and secret are the same as the saved ones i.e. same connect.
	 *
	 * @param string $api_key, $api_secret
	 */
	private function is_key_match( $api_key, $api_secret ) {
		if( ($this->api_key == $api_key) && ($this->api_secret == $api_secret) ) {
			return true;
		} else {
			return false;
		}		
	}

	/**
	 * Quipu_Api constructor.
	 *
	 * @param string $api_key, $api_secret
	 */
	private function __construct( $api_key, $api_secret ) {
		$this->api_key = $api_key;
		$this->api_secret = $api_secret;
	}

	// Magic method clone is empty to prevent duplication of connection
	private function __clone() { }
   	
   	// Stopping unserialize of object
	private function __wakeup() { }

	/**
	 * @return string
	 */
	protected function get_access_token() {
		if( $this->is_access_token_empty() || $this->is_token_expired() ) {
			if($this->request_access_token() === false) {
				return false;
			}
		}
		
		return $this->access_token;
	}

	/**
	 * @param string $access_token
	 */
	protected function set_access_token( $access_token ) {
		$this->access_token = $access_token;
	}

	/**
	 * @return bool
	 */
	protected function is_access_token_empty() {
		return empty($this->access_token);
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

	/**
	 * Check if key files exist
	 *
	 * @return bool
	 */
	private function do_keys_exist() {

		// Check keys
		if ( empty($this->api_key) || empty($this->api_secret) ) {
			$this->error_msg = 'API key or secret NOT set.';
			return false;
		}

		return true;
	}

	/**
	 * Get the signed URL.
	 * The signed URL is fetched by doing an OAuth request.
	 *
	 * @throws Exception
	 *
	 * @return String
	 */
	private function is_token_expired() {		
		if( $this->token_expires < time() ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get the signed URL.
	 * The signed URL is fetched by doing an OAuth request.
	 *
	 * @throws Exception
	 *
	 * @return String
	 */
	private function request_access_token() {
		$curl = curl_init( self::API_URL.self::AUTH_URL );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		$credentials = base64_encode($this->api_key . ":" . $this->api_secret);
		curl_setopt($curl, CURLOPT_USERPWD, $credentials);
		curl_setopt($curl, CURLOPT_HEADER,'Content-Type: application/x-www-form-urlencoded;charset=UTF-8');
		curl_setopt($curl, CURLOPT_POSTFIELDS, array(
		    'grant_type' => 'client_credentials',
		    'scope' => 'ecommerce'
		) );
		
		$auth = curl_exec( $curl );
		$secret = json_decode($auth); // NOTE: json_decode returns an object here unlike in requests
		curl_close($curl);

		if(isset($secret->error)) {
			$this->error_msg = $secret->error;
			return false;
		} else {
			// Set token expires time to check if it has expired before making an API call
			$this->token_expires = time() + $secret->expires_in;
			$this->access_token = $secret->access_token;
			return true;	
		}	
	}

	private function init_request($url) {
		// Check if required settings are set
		if ( false === $this->do_keys_exist() ) {
			return false;
		}

		// Get access token to make API call
		$access_token = $this->get_access_token();
		if( $access_token === false ) {
			return false;
		}
		
		$this->curl = curl_init( $url );	
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer ' . $access_token, 'Accept: application/vnd.quipu.v1+json', 'Content-Type: application/vnd.quipu.v1+json' ) );

		return true;
	}

	private function finish_request() {
		$response = curl_exec( $this->curl );
		curl_close($this->curl);
		
		if($response) {
			$req_json = json_decode($response, true);

			if(isset($req_json['errors'])) {
				$this->error_msg = $req_json['errors'][0]['detail']." => ".$req_json['errors'][0]['source']['pointer'];
				return false;
			} else {
				return $req_json;	
			}	

		} else {
			$this->error_msg = 'Empty Response';
			return false;
		}
	}

	/**
	 * Do the request
	 *
	 * @throws Exception
	 * @param string $response_type
	 * @param API endpoint and post data
	 *
	 * @return bool
	 */
	public function post_request($endpoint, $post_data) {
		
		if($this->init_request(self::API_URL.$endpoint) === false ) {
			throw new Exception($this->error_msg);
		}
		
       	curl_setopt($this->curl, CURLOPT_POST, true);
       	curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($post_data) );
		
		/*
		    case "PUT":
		        curl_setopt($ch, CURLOPT_PUT, true);
		        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($attr) );
		        break;
		    case "DELETE":
		        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
		        break;
		*/

		$res = $this->finish_request();
		if(!$res) {
			throw new Exception("POST: ".$endpoint." - Error: ".$this->error_msg." - Args: ".print_r($post_data,true));
		}

		return $res;
	}

	/**
	 * Do the request
	 *
	 * @throws Exception
	 * @param string $response_type
	 * @param API endpoint and post data
	 *
	 * @return bool
	 */
	public function get_request($endpoint) {
		
		if($this->init_request(self::API_URL.$endpoint) === false ) {
			throw new Exception($this->error_msg);
		}

		$res = $this->finish_request();
		if(!$res) {
			throw new Exception("GET: ".$endpoint." - Error: ".$this->error_msg);
		}

		return $res;
	}

}
