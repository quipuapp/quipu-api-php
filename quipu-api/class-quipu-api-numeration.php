<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Quipu_Api' ) ) {
	include_once( 'class-quipu-api.php' );
}

class Quipu_Api_Numeration extends Quipu_Api {

	public function __construct( Quipu_Api_Connection $api_connection ) {
		parent::__construct( $api_connection );

		// Set Endpoint
		$this->set_endpoint( 'numbering_series' );
	}

	public function create_series($prefix, $amending = false) {

		if(empty($prefix)) {
			throw new Exception('Create: passed prefix variable is empty.');
		}		

		try {
			// Try to fetch existing prefix
			$this->get_series($prefix, $amending);

			if(empty($this->id)) {
				$postData = array(
				    "data" => array(
				    	"type" => "numbering_series",
				    	"attributes" => array(
				    		"prefix" => "$prefix",
				            "applicable_to" => "invoices",
				            "amending" => $amending,
				            "default" => false
						)	    	
				    )
				);

				$this->create_request($postData);
			}
		} catch (Exception $e) {
			throw $e;
		} 
		
	}

	public function create_refund_series($prefix) {
		try {
			$this->create_series($prefix, true);
		} catch (Exception $e) {
			throw $e;
		}		
	}

	public function get_series($prefix, $amending) {
		if(empty($prefix)) {
			throw new Exception('Get: passed prefix variable is empty.');
		}

		try {
			$this->get_filter_request("?filter[prefix]=$prefix&filter[amending]=$amending");
		} catch (Exception $e) {
			throw $e;
		} 	
	}
}
