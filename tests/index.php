<?php

//require
require_once '../../../autoload.php';
require_once 'config.php';

use \TijsVerkoyen\Factr\Factr;

// create instance
$factr = new Factr(USERNAME, PASSWORD);

$client = array(
    'email' => 'factr@verkoyen.eu',
    'first_name' => 'Tijs',
    'last_name' => 'Verkoyen',
    'company' => 'Sumo Coders',
    'billing_address' => array(
        'street' => 'Kerkstraat',
        'number' => '108',
        'city' => '9050',
        'zip' => 'Gentbrugge',
        'country' => 'BE'
    ),
    'company_address' => array(
        'street' => 'Kerkstraat',
        'number' => '108',
        'city' => '9050',
        'zip' => 'Gentbrugge',
        'country' => 'BE'
    ),
    'vat' => 'BE 0829.564.289'
);

$invoice = array(
    'client_id' => 2292,
    'items' => array(
        array(
            'description' => 'foo',
            'price' => 123.45,
            'amount' => 67,
            'vat' => 21
        ),
        array(
            'description' => 'bar',
            'price' => 543.21,
            'amount' => 76,
            'vat' => 6
        )
    )
);

try {
 $response = $factr->clients();
 $response = $factr->clientsGet(2292);
 $response = $factr->clientsCreate($client);
 $response = $factr->invoices();
 $response = $factr->invoicesGet(5258);
 $response = $factr->invoicesGetByIid('IV08004');
 $response = $factr->invoicesCreate($invoice);
 $response = $factr->invoiceSendByMail(5261, 'foo@bar.com');
 $response = $factr->invoicesAddPayment(5261, 123.45);
} catch (Exception $e) {
    var_dump($e);
}

// output
var_dump($response);
