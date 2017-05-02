## Quipu API PHP ##

PHP classes that connect to the Quipu API see [documentation](http://quipuapp.github.io/api-v1-docs/)

### Requirements

- PHP 5.3 or higher
- cURL
- Quipu Account

### Getting Started

#### Create a connection

The Quipu_Api_Connection class is a singleton class and will keep the connection open for 2 hours.  The connection instance must be passed to each class.

<pre><code>$api_connection = Quipu_Api_Connection::get_instance('YOUR_APP_KEY', 'YOUR_APP_SECRET');</code></pre>

#### Create a Numbering Series
Pass the connection class to the numeration class, then call "create_series".  The series will either be created or loaded if it already exists.

<pre><code>$quipu_num = new Quipu_Api_Numeration($api_connection);
$quipu_num->create_series('YOUR_PREFIX');</code></pre>

#### Create a Contact
The following parameters can be passed to create a contact.  
- The only required field is "name".  
- If "tax_id" is passed the class will try to load the contact if they already exist in Quipu.
- "country_code" should use [ISO 3166-1 alpha-2] (https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2)

<pre><code>
$contact = array(
	"name" => "CONTACT_NAME",
	"tax_id" => "CONTACT_VAT_ID",
	"phone" => "CONTACT_PHONE ",
	"email" => "CONTACT_EMAIL",
	"address" => "CONTACT_ADDRESS",
	"town" => "CONTACT_CITY",
	"zip_code" => "CONTACT_ZIP_CODE",
	"country_code" => "CONTACT_CODE",
	"bank_account_number" => "IBAN"
);
</code></pre>

Pass the connection class to the contact class, then call "create_contact", with the array above.  The contact will either be created or loaded if they already exist.

<pre><code>$quipu_contact = new Quipu_Api_Contact($api_connection);
$quipu_contact->create_contact($contact);</code></pre>

#### Create an Invoice
The following parameters can be passed to create an invoice.  
- "issue_date" is required
- "items" are required. "items" is an array with at least one value, all its variables are required
  - "product" is the item name
  - "cost" is the value in Euros
  - "quantity" must be at least 1
  - "vat_per" is the VAT percentage (not value)
- The accepted values for "payment_method" are detailed in the [Quipu API documentation](http://quipuapp.github.io/api-v1-docs/?shell#attributes)

<pre><code>
$order = array(
	"payment_method" => "PAYMENT_METHOD",
	"issue_date" => "YYYY-mm-dd",
	"items" => array(
		"product" => "PRODUCT_NAME",
		"cost" => "PRODUCT_PRICE",
		"quantity" => "PRODUCT_QUANTITY",
		"vat_per" => "VAT_PERCENTAGE"
	);
);
</code></pre>

Pass the connection class to the invoice class:

<pre><code>$quipu_invoice = new Quipu_Api_Invoice($api_connection);</code></pre>

You can first set the Numbering Series if one exists:

<pre><code>$quipu_invoice->set_numeration($quipu_num);</code></pre>

A contact is required or the invoice cannot be created:

<pre><code>$quipu_invoice->set_contact($quipu_contact);</code></pre>

Once the contact is passed to the class you can create an invoice

<pre><code>$quipu_invoice->create_invoice($order);</code></pre>

To get the internal Quipu Invoice id.  Store locall to use for refunds.
<pre><code>$quipu_invoice->get_id()</code></pre>

#### Create a Refund
The following parameters can be passed to create a refund.  
- "refund_date" is required
- "invoice_id" is required.  This is the Quipu invoice id returned after creating an invoice 
- "items" are NOT required. If "items" are not passed then the assumption is the whole invoice is being refunded, otherwise the assumption is it is a partial refund:
  - "product" is the item name
  - "cost" is the value in Euros
  - "quantity" must be at least 1
  - "vat_per" is the VAT percentage (not value)

<pre><code>
$order = array(
	"invoice_id" => "QUIPU_INVOICE_ID",
	"refund_date" => "YYYY-mm-dd",
	"items" => array(
		"product" => "PRODUCT_NAME",
		"cost" => "PRODUCT_PRICE",
		"quantity" => "PRODUCT_QUANTITY",
		"vat_per" => "VAT_PERCENTAGE"
	);
);
</code></pre>

Pass the connection class to the invoice class:

<pre><code>$quipu_invoice = new Quipu_Api_Invoice($api_connection);</code></pre>

You can first set the Refund Numbering Series if one exists:

<pre><code>$quipu_invoice->set_numeration($refund_num_series);</code></pre>

Call the 'refund_invoice' function to refund an invoice

<pre><code>$quipu_invoice->refund_invoice($order);</code></pre>

### Changelog

#### 1.0
* First public release
