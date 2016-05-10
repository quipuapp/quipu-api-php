<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Quipu_Api' ) ) {
	include_once( 'class-quipu-api.php' );
}

class Quipu_Api_Contact extends Quipu_Api {

	public function __construct( Quipu_Api_Connection $api_connection ) {
		parent::__construct( $api_connection );

		// Set Endpoint
		$this->set_endpoint( 'contacts' );
	}

	private function __create_contact($contact) {
		if(empty($contact['name'])){
			throw new Exception('Create: no contact name passed.');
		}

		try {
			$postData = array(
			    "data" => array(
			    	"type" => "contacts",
			    	"attributes" => array(
			    		"name" => "$contact[name]",
			            "tax_id" => "$contact[tax_id]",
			            "phone" => "$contact[phone]",
			            "email" => "$contact[email]",
			            "address" => "$contact[address]",
			            "town" => "$contact[town]",
			            "zip_code" => "$contact[zip_code]",
			            "country_code" => "$contact[country_code]"
					)  	
			    )
			);

			$this->create_request($postData);
		} catch (Exception $e) {
			throw $e;
		} 
		
	}

	public function create_contact($contact) {
		try {
			if($contact['tax_id']) {
				$this->get_contact($contact['tax_id']);

				if(empty($this->id)) {
					$this->__create_contact($contact);					
				}
			} else {
				$this->__create_contact($contact);	
			}
		} catch (Exception $e) {
			throw $e;
		} 		
	}

	public function get_contact($tax_id) {
		if(empty($tax_id)) {
			throw new Exception('Get: no tax id passed.');
		}
		return $this->get_filter_request("?filter[tax_id]=$tax_id");
	}

}
