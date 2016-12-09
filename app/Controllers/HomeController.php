<?php

namespace App\Controllers;

use App\Models\CNAM;
use App\Models\User;


class HomeController extends Controller
{

    public function index($request, $response, $args)
    {
      $invoiceKey = trim($args['invoiceURLKey']);
    	$invoiceKey = filter_var($invoiceKey, FILTER_SANITIZE_STRING);

      $_SESSION['invoiceKey'] = $invoiceKey;

    	$db = $this->container->db;
      
      //invoice
      $db->join("invoice_amounts inta", "inta.invoice_id=ivn.invoice_id", "LEFT");
      $db->where('invoice_url_key', $invoiceKey);
      $invoice = $db->get('invoices ivn');

      //invoice items
      $db->where('invoice_id', $invoice[0]['invoice_id']);
      $db->orderBy('item_order', 'ASC');
      $invoiceItems = $db->get('invoice_items');

      //client
      $db->where('client_id', $invoice[0]['client_id']);
      $client = $db->get('clients');
      
      //var_dump($invoice);

      $dueDate = new \DateTime($invoice[0]['invoice_date_due']);
      $now     = new \DateTime('now');
      $interval = $dueDate->diff($now);

      $data = [
        'stripe_pub_key' => STRIPE_PUB_KEY,
        'invoice_status_id' => $invoice[0]['invoice_status_id'],
        'invoiceKey'       => $invoiceKey,
        'invoice_date_created' => date_format(date_create($invoice[0]['invoice_date_created']), "F d, Y"),
        'invoice_date_due' => date_format(date_create($invoice[0]['invoice_date_due']), "F d, Y"),
        'invoice_number' => $invoice[0]['invoice_number'],
        'invoice_item_subtotal' => $invoice[0]['invoice_item_subtotal'],
        'invoice_tax_total' => $invoice[0]['invoice_tax_total'],
        'invoice_total' => $invoice[0]['invoice_total'],
        'invoice_paid' => $invoice[0]['invoice_paid'],
        'invoice_balance' => $invoice[0]['invoice_balance'],
        'invoiceItems'  => $invoiceItems,
        'invoice_overdue' => $now > $dueDate,
        'client_name' => $client[0]['client_name'],
        'client_address_1' => $client[0]['client_address_1'],
        'client_address_2' => $client[0]['client_address_2'],
        'client_city' => $client[0]['client_city'],
        'client_state' => $client[0]['client_state'],
        'client_zip' => $client[0]['client_zip'],
        'client_phone' => $client[0]['client_phone'],
        'client_email' => $client[0]['client_email'],
        'client_web' => $client[0]['client_web']
      
      ];
        
        
      return $this->view->render($response, 'home.twig', $data);
    }

    public function noURLKey($request, $response)
    {
        
    }

    public function paid($request, $response, $args)
    {
        if(empty($_SESSION['charge'])){
          return $response->withRedirect($this->router->pathFor('home', ['invoiceURLKey' => $args['invoiceURLKey']]));
        }

        $db = $this->container->db;
        //invoice
        $db->join("invoice_amounts inta", "inta.invoice_id=ivn.invoice_id", "LEFT");
        $db->where('invoice_url_key', $_SESSION['invoiceKey']);
        $invoice = $db->get('invoices ivn');

        \Stripe\Stripe::setApiKey(STRIPE_KEY);
        $c = \Stripe\Charge::retrieve($_SESSION['charge']);

        $data = ['seller_message' => $c->outcome['seller_message'], 
                  'invoice_number' => $invoice[0]['invoice_number'], 
                  'receipt_email' => $c->receipt_email
                ];

        unset($_SESSION['charge']);
        unset($_SESSION['invoiceKey']);

        return $this->view->render($response, 'paid.twig', $data);
    }

    public function stripetoken($request, $response, $args)
    {
        $invoiceKey = trim($args['invoiceURLKey']);
        $invoiceKey = filter_var($invoiceKey, FILTER_SANITIZE_STRING);

        $db = $this->container->db;
        //invoice
        $db->join("invoice_amounts inta", "inta.invoice_id=ivn.invoice_id", "LEFT");
        $db->where('invoice_url_key', $invoiceKey);
        $invoice = $db->get('invoices ivn');

        //client
        $db->join("client_custom cc", "cc.client_id=cs.client_id", "LEFT");
        $db->where('cs.client_id', $invoice[0]['client_id']);
        $client = $db->get('clients cs');

        //remove the decimal to conver to cents
        $blanceInCents = $invoice[0]['invoice_balance'] * 100;  
        
        \Stripe\Stripe::setApiKey(STRIPE_KEY);

        $token  = $request->getParam('stripeToken');

        //find the customer id //if none, create one
        if(!$client[0]['client_custom_stripe_customer_id'])
        {
            $customer = \Stripe\Customer::create(array(
            'email' => $client[0]['client_email'],
            'source'  => $token
            ));

            $d = ['client_custom_stripe_customer_id' => $customer->id];
            $db->where ('client_id', $client[0]['client_id']);
            $db->update ('client_custom', $d);
            
        } else {
            $customer = \Stripe\Customer::retrieve($client[0]['client_custom_stripe_customer_id']);
        }
        
        try{
          $charge = \Stripe\Charge::create(array(
              'customer' => $customer->id,
              'amount'   => $blanceInCents,
              'currency' => 'usd',
              'receipt_email' => $client[0]['client_email'],
              'metadata' => array('invoice_number' => $invoice[0]['invoice_number'])
          ));
          $_SESSION['charge'] = $charge->id;
        } catch(\Stripe\Error\Card $e) {
          var_dump($e);

          $this->flash->addMessage('error', 'Could not sign you in with those details.');
          return $response->withRedirect($this->router->pathFor('home', ['invoiceURLKey' => $invoiceKey]));
        }

        return $response->withRedirect($this->router->pathFor('paid', ['invoiceURLKey' => $invoiceKey]));
    }
    
}
