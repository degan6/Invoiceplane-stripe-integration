# InvoicePlane Stripe Integration

This app integrates with [InvoicePlane](https://invoiceplane.com/) to gernerate unquine links for clints to pay invocies with credit cards using [Stripe](https://stripe.com/).

This program is a hack, it retrives information dirrectly from InvoicesPlane's database, it does not use any formal API. 

It does not create payments in InvoicePlane. It does however write the Stripe customerID to a custom field on the client in InvoicePlane. It uses this ID to connect Stripe customers with clients in InvoicePlane.

## Getting Started

### Prerequisites

* InvoicePlane installed and current database credentials.
* A Stripe account

### Installing

1. Create a custom field in InvoicePlane under the Client table called Stripe Customer ID.

2. Git clone the repo, create a vhost and point too /public/

3. **Highly Recommended:**

  Create a separate user with SELECT privileges on the clients, invoices, invoice_amounts, invoice_items, invoice_item_amounts, users tables

  And update on the client_custom table.

4. Create /bootstrap/config.php and populate it with:
  ```php
  <?php
  
  //The InvoicePlane database and a user with SELECT AND INSERT Privileges
  define('DB_HOST', ''); 
  define('DB_NAME', '');
  define('DB_USER', '');
  define('DB_PASS', '');

  define('USER_ID', '1'); //The User that the app will pull company data from when displaying on invoice

  define('DEBUG', TRUE);

  if (DEBUG){
	  define('STRIPE_PUB_KEY', ''); //Stripe.com Publishable Test Key
	  define('STRIPE_KEY', '');     //Stripe.com Secret Test Key
  } else {
	  define('STRIPE_PUB_KEY', '' ); //Stripe.com Publishable Test Key
	  define('STRIPE_KEY', '');      //Stripe.com Secret Test Key
  }
  ```
5. Add your Stripe Test and Live Keys.

6. Set DEBUG to true and test apps flow with Stripes test cards.

7. Set DEBUG to false, and get paid.

###Create Unquie Links
The app uses URL Key created by InvoicePlane to refrence invoices, this key on the end of the Guest URL when viewing the invoice when viewing the invoice after it has been marked as sent in InvoicePlane.

Example: https://invoice.example.com/guest/view/invoice/k5ja9SsWZdW4ixR

URL Key: k5ja9SsWZdW4ixR

URL to send to Client: https://pay.appurl.com/invoice/k5ja9SsWZdW4ixR


## Motivation

I love InovicePlane but I needed a way to take credit cards. Currently InvoicePlane does not support any vendors expect Paypal.

## Contributors

* [David Egan](https://degan.org/)

Pull requests welcome.

## Built With

* [InvoicePlane](https://invoiceplane.com/) - Invoiceing system used to generation invoices.
* [Slim Micro Framework](https://www.slimframework.com/) - PHP Framework Used
* [Twig Templating Engine](http://twig.sensiolabs.org/) - Templating Engine
* [PHP-MySQLi-Database-Class](https://github.com/joshcam/PHP-MySQLi-Database-Class#select-query) - MYSQL PHP Wrapper Class written by Josh Campbell

## License

This proeject is licensed under the MIT License.

The name InvoicePlane is copyright of InvoicePlane.com / Kovah.de. This project is not sanctioned by either party. 
