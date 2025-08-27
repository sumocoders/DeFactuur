<?php

require_once realpath(__DIR__ . '/../vendor/autoload.php');
require_once realpath(__DIR__ . '/config.php');

use \SumoCoders\DeFactuur\DeFactuur;
use \SumoCoders\DeFactuur\Client\Client;
use \SumoCoders\DeFactuur\Client\Address;
use \SumoCoders\DeFactuur\Invoice\Invoice;
use \SumoCoders\DeFactuur\Invoice\Item;
use \SumoCoders\DeFactuur\Invoice\Payment;

// create instance
$factr = new DeFactuur(
    new \Symfony\Component\HttpClient\Psr18Client(),
    new \Nyholm\Psr7\Factory\Psr17Factory(),
    new \Nyholm\Psr7\Factory\Psr17Factory(),
);
$factr->setApiToken(API_TOKEN);
//$token = $factr->accountApiToken('demo@defactuur.be', 'demo');

$address = new Address();
$address->setCountry('BE');
$address->setFullAddress('Kerkstraat 108' . "\n" . '9050 Gentbrugge');

$client = new Client(
    ['foo@bar.com'],
    $address,
    $address,
    null,
    'company',
    'VAT'
);
$client->setPaymentDays(30);
$client->setCid('CID' . time());
$client->setFirstName('first_name');
$client->setPhone('phone');
$client->setFax('fax');
$client->setCell('cell');
$client->setWebsite('website');
$client->setRemarks('remarks');
$client->addEmail('e@mail.com');
$client->setInvoiceableByEmail(false);
$client->setInvoiceableBySnailMail(false);
$client->setInvoiceableByFactr(false);

$item = new Item(
    'just an item',
    12,
);
$item->setAmount(12);
$item->setVat(21);

$payment = new Payment();
$payment->setAmount(12.34);
$payment->setIdentifier('identifier');
$payment->setPaidAt(new DateTime('@' . mktime(12, 13, 14, 6, 20, 2014)));

$invoice = new Invoice();
$invoice->addItem($item);
$invoice->setClientId(3026);
$invoice->setDescription('description');
$invoice->setShownRemark('shown_remark');
$invoice->setState(\SumoCoders\DeFactuur\ValueObject\State::created());
$invoice->setPaymentMethod('not_paid');

try {
//    $response = $factr->accountApiToken(USERNAME, PASSWORD);

//    $response = $factr->clients();
//    $response = $factr->clientsGet(3026);
//    $response = $factr->clientsCreate($client);
//    $client->setRemarks('Updated by the wrapper class');
//    $response = $factr->clientsUpdate(3026, $client);
//    $response = $factr->clientsDelete(123);
//    $response = $factr->clientsInvoices(2703);
//    $response = $factr->peppolSearch('FooBar');

//    $response = $factr->invoices();
//    $response = $factr->invoicesGet(9256);
//    $response = $factr->invoicesGetByIid('IV08004');
//    $response = $factr->invoicesCreate($invoice);
//    $invoice->setDescription('Updated by the wrapper class');
//    $response = $factr->invoicesUpdate(9256, $invoice);
//    $response = $factr->invoiceSendByMail(5261, 'foo@bar.com');
//    $response = $factr->invoicesAddPayment(5261, $payment);
//    $response = $factr->invoicesDelete($response->getId());
} catch (Exception $e) {
    var_dump($e);
}

// output
var_dump($response);
