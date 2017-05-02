<?php

if (!defined('ABSPATH')) {
    exit;
}
include_once('class-quipu-api.php');

class Quipu_Api_Contact extends Quipu_Api
{

    public function __construct(Quipu_Api_Connection $api_connection)
    {
        parent::__construct($api_connection);
    }

    public function create_contact($contact, $update = false)
    {
        try {
            if ($contact['tax_id']) {
                $this->get_contact($contact['tax_id']);

                if (empty($this->id)) {
                    $this->__create_contact($contact);
                } elseif (true === $update) {
                    $this->__update_contact($contact);
                }
            } else {
                $this->__create_contact($contact);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function get_contact($tax_id)
    {
        if (empty($tax_id)) {
            throw new Exception('Get: no tax id passed.');
        }
        $this->set_endpoint('contacts');

        return $this->get_filter_request("?filter[tax_id]=$tax_id");
    }

    private function __create_contact($contact)
    {
        $this->set_endpoint('contacts');
        // Check if contact name is empty spaces
        $trim_contact = trim($contact['name']);
        if (empty($trim_contact)) {
            throw new Exception('Create: no contact name passed.');
        }

        $contact = $this->validateContactData($contact);

        try {
            $postData = [
                "data" => [
                    "type" => "contacts",
                    "attributes" => [
                        "name" => "$contact[name]",
                        "tax_id" => "$contact[tax_id]",
                        "phone" => "$contact[phone]",
                        "email" => "$contact[email]",
                        "address" => "$contact[address]",
                        "town" => "$contact[town]",
                        "zip_code" => "$contact[zip_code]",
                        "country_code" => "$contact[country_code]",
                        "supplier_number" => null,
                        "is_supplier_of_direct_goods" => (boolean)$contact['is_supplier_of_direct_goods'],
                        "bank_account_number" => "$contact[bank_account_number]",
                        "bank_account_swift_bic" => "$contact[bank_account_swift_bic]",
                        "sepa_signature_date" => "$contact[sepa_signature_date]",
                    ],
                ],
            ];

            $this->create_request($postData);
        } catch (Exception $e) {
            throw $e;
        }

    }

    /**
     * @param $contact
     *
     * @return mixed
     */
    private function validateContactData($contact)
    {
        $contact['country_code'] = strtolower($contact['country_code']);
        $contact['is_supplier_of_direct_goods'] = $contact['is_supplier_of_direct_goods'] ? true : false;

        return $contact;
    }

    private function __update_contact($contact)
    {
        $this->set_endpoint('contacts/'.$this->id);
        $contact = $this->validateContactData($contact);

        try {
            $postData = [
                "data" => [
                    "type" => "contacts",
                    "attributes" => [
                    ],
                ],
            ];
            $attributes = [];
            if ($contact['name']) {
                $attributes["name"] = "$contact[name]";
            }
            if ($contact['tax_id']) {
                $attributes["tax_id"] = "$contact[tax_id]";
            }
            if ($contact['phone']) {
                $attributes["phone"] = "$contact[phone]";
            }
            if ($contact['email']) {
                $attributes["email"] = "$contact[email]";
            }
            if ($contact['address']) {
                $attributes["address"] = "$contact[address]";
            }
            if ($contact['town']) {
                $attributes["town"] = "$contact[town]";
            }
            if ($contact['zip_code']) {
                $attributes["zip_code"] = "$contact[zip_code]";
            }
            if ($contact['country_code']) {
                $attributes["country_code"] = "$contact[country_code]";
            }
            if ($contact['supplier_number']) {
                $attributes["supplier_number"] = "$contact[supplier_number]";
            }
            if ($contact['is_supplier_of_direct_goods']) {
                $attributes["is_supplier_of_direct_goods"] = (boolean)$contact['is_supplier_of_direct_goods'];
            }
            if ($contact['bank_account_number']) {
                $attributes["bank_account_number"] = "$contact[bank_account_number]";
            }
            if ($contact['bank_account_swift_bic']) {
                $attributes["bank_account_swift_bic"] = "$contact[bank_account_swift_bic]";
            }
            if ($contact['sepa_signature_date']) {
                $attributes["sepa_signature_date"] = "$contact[sepa_signature_date]";
            }
            $postData['data']['attributes'] = $attributes;
            $this->update_request($postData);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
