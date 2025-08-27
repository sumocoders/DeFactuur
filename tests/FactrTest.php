<?php

namespace SumoCoders\DeFactuur\tests;

require_once __DIR__ . '/../../../autoload.php';
require_once 'config.php';

use \SumoCoders\DeFactuur\DeFactuur;

/**
 * test case.
 */
class FactrTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DeFactuur
     */
    private $factr;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->factr = new DeFactuur();
        $this->factr->setApiToken(API_TOKEN);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->factr = null;
        parent::tearDown();
    }

    /**
     * @return \SumoCoders\DeFactuur\Client\Address
     */
    public function createAddress()
    {
        $address = new \SumoCoders\DeFactuur\Client\Address();
        $address->setFullAddress('Kerkstraat 108 9050 Gentbrugge');
        $address->setCountry('BE');

        return $address;
    }

    /**
     * Tests DeFactuur->getTimeOut()
     */
    public function testGetTimeOut()
    {
        $this->factr->setTimeOut(5);
        $this->assertEquals(5, $this->factr->getTimeOut());
    }

    /**
     * Tests DeFactuur->getUserAgent()
     */
    public function testGetUserAgent()
    {
        $this->factr->setUserAgent('testing/1.0.0');
        $this->assertEquals('PHP DeFactuur/' . DeFactuur::VERSION . ' testing/1.0.0', $this->factr->getUserAgent());
    }

    /**
     * Tests DeFactuur->accountApiToken()
     */
    public function testAccountApiToken()
    {
        $response = $this->factr->accountApiToken(USERNAME, PASSWORD);
        $this->assertInternalType('string', $response);
    }

    /**
     * Tests DeFactuur->clients
     */
    public function testClients()
    {
        $response = $this->factr->clients();
        $this->assertInternalType('array', $response);
        foreach ($response as $item) {
            $this->assertInstanceOf('\SumoCoders\DeFactuur\Client\Client', $item);
        }
    }

    /**
     * Tests DeFactuur->clientsCreate
     */
    public function testClientsCreate()
    {
        $address = $this->createAddress();

        $client = new \SumoCoders\DeFactuur\Client\Client();
        $client->setFirstName('Tijs');
        $client->setLastName('Verkoyen');
        $client->addEmail('php-factr@verkoyen.eu');
        $client->setBillingAddress($address);
        $client->setCompanyAddress($address);
        $client->setRemarks('Created by the Wrapper-class. ' . time());
        $client->setCell('cell');

        $response = $this->factr->clientsCreate($client);

        $this->assertInstanceOf('\SumoCoders\DeFactuur\Client\Client', $response);

        // cleanup
        $this->factr->clientsDelete($response->getId());
    }

    /**
     * Tests DeFactuur->clientsUpdate
     */
    public function testClientsUpdate()
    {
        $address = $this->createAddress();

        $client = new \SumoCoders\DeFactuur\Client\Client();
        $client->setFirstName('Tijs');
        $client->setLastName('Verkoyen');
        $client->addEmail('php-factr@verkoyen.eu');
        $client->setBillingAddress($address);
        $client->setCompanyAddress($address);
        $client->setRemarks('Created by the Wrapper-class. ' . time());

        $response = $this->factr->clientsCreate($client);
        $id = $response->getId();
        $client->setRemarks('Updated by the Wrapper-class. ' . time());
        $response = $this->factr->clientsUpdate($id, $client);

        $this->assertTrue($response);

        // cleanup
        $this->factr->clientsDelete($id);
    }

    /**
     * Tests DeFactuur->clientsDelete
     */
    public function testClientsDelete()
    {
        $address = $this->createAddress();

        $client = new \SumoCoders\DeFactuur\Client\Client();
        $client->setFirstName('Tijs');
        $client->setLastName('Verkoyen');
        $client->addEmail('php-factr@verkoyen.eu');
        $client->setBillingAddress($address);
        $client->setCompanyAddress($address);
        $client->setRemarks('Created by the Wrapper-class. ' . time());

        $response = $this->factr->clientsCreate($client);
        $response = $this->factr->clientsDelete($response->getId());

        $this->assertTrue($response);
    }

    /**
     * Tests DeFactuur->clientsGet
     */
    public function testClientsGet()
    {
        $address = $this->createAddress();

        $client = new \SumoCoders\DeFactuur\Client\Client();
        $client->setFirstName('Tijs');
        $client->setLastName('Verkoyen');
        $client->addEmail('php-factr@verkoyen.eu');
        $client->setBillingAddress($address);
        $client->setCompanyAddress($address);
        $client->setRemarks('Created by the Wrapper-class. ' . time());

        $response = $this->factr->clientsCreate($client);
        $id = $response->getId();
        $response = $this->factr->clientsGet($id);

        $this->assertInstanceOf('\SumoCoders\DeFactuur\Client\Client', $response);

        // cleanup
        $this->factr->clientsDelete($id);
    }

    /**
     * Tests DeFactuur->clientsInvoices
     */
    public function testClientsInvoices()
    {
        $response = $this->factr->invoices();
        $paidInvoice = null;
        foreach ($response as $invoice) {
            if ($invoice->getState() == 'paid') {
                $paidInvoice = $invoice;
                break;
            }
        }

        if ($paidInvoice === null) {
            $this->markTestSkipped('No paid invoices found');
        }

        $response = $this->factr->clientsInvoices($paidInvoice->getClientId());
        $this->assertInternalType('array', $response);
        foreach ($response as $item) {
            $this->assertInstanceOf('\SumoCoders\DeFactuur\Invoice\Invoice', $item);
        }
    }

    /**
     * Tests DeFactuur->invoices
     */
    public function testInvoices()
    {
        $response = $this->factr->invoices();
        $this->assertInternalType('array', $response);
        foreach ($response as $item) {
            $this->assertInstanceOf('\SumoCoders\DeFactuur\Invoice\Invoice', $item);
        }
    }

    /**
     * Tests DeFactuur->invoicesAddPayment
     */
    public function testInvoicesAddPayment()
    {
        $address = $this->createAddress();

        $client = new \SumoCoders\DeFactuur\Client\Client();
        $client->setFirstName('Tijs');
        $client->setLastName('Verkoyen');
        $client->addEmail('php-factr@verkoyen.eu');
        $client->setBillingAddress($address);
        $client->setCompanyAddress($address);
        $client->setRemarks('Created by the Wrapper-class. ' . time());

        $client = $this->factr->clientsCreate($client);

        $item = new \SumoCoders\DeFactuur\Invoice\Item();
        $item->setDescription('just an item');
        $item->setPrice(123.45);
        $item->setAmount(67);
        $item->setVat(21);

        $invoice = new \SumoCoders\DeFactuur\Invoice\Invoice();
        $invoice->setDescription('Created by the Wrapper-class. ' . time());
        $invoice->setClient($this->factr->clientsGet($client->getId()));
        $invoice->addItem($item);

        $invoice = $this->factr->invoicesCreate($invoice);

        $payment = new \SumoCoders\DeFactuur\Invoice\Payment();
        $payment->setAmount(10);

        $response = $this->factr->invoicesAddPayment($invoice->getId(), $payment);
        $this->assertInstanceOf('\SumoCoders\DeFactuur\Invoice\Payment', $response);

        // cleanup
        $this->factr->invoicesDelete($invoice->getId());
        $this->factr->clientsDelete($client->getId());
    }

    /**
     * Tests DeFactuur->invoicesCreate
     */
    public function testInvoicesCreate()
    {
        $address = $this->createAddress();

        $client = new \SumoCoders\DeFactuur\Client\Client();
        $client->setFirstName('Tijs');
        $client->setLastName('Verkoyen');
        $client->addEmail('php-factr@verkoyen.eu');
        $client->setBillingAddress($address);
        $client->setCompanyAddress($address);
        $client->setRemarks('Created by the Wrapper-class. ' . time());

        $client = $this->factr->clientsCreate($client);

        $item = new \SumoCoders\DeFactuur\Invoice\Item();
        $item->setDescription('just an item');
        $item->setPrice(123.45);
        $item->setAmount(67);
        $item->setVat(21);

        $invoice = new \SumoCoders\DeFactuur\Invoice\Invoice();
        $invoice->setDescription('Created by the Wrapper-class. ' . time());
        $invoice->setClient($this->factr->clientsGet($client->getId()));
        $invoice->addItem($item);

        $invoice = $this->factr->invoicesCreate($invoice);
        $this->assertInstanceOf('\SumoCoders\DeFactuur\Invoice\Invoice', $invoice);

        // cleanup
        $this->factr->invoicesDelete($invoice->getId());
        $this->factr->clientsDelete($client->getId());
    }

    /**
     * Tests DeFactuur->invoicesUpdate
     */
    public function testInvoicesUpdate()
    {
        $address = $this->createAddress();

        $client = new \SumoCoders\DeFactuur\Client\Client();
        $client->setFirstName('Tijs');
        $client->setLastName('Verkoyen');
        $client->addEmail('php-factr@verkoyen.eu');
        $client->setBillingAddress($address);
        $client->setCompanyAddress($address);
        $client->setRemarks('Created by the Wrapper-class. ' . time());

        $client = $this->factr->clientsCreate($client);

        $item = new \SumoCoders\DeFactuur\Invoice\Item();
        $item->setDescription('just an item');
        $item->setPrice(123.45);
        $item->setAmount(67);
        $item->setVat(21);

        $invoice = new \SumoCoders\DeFactuur\Invoice\Invoice();
        $invoice->setDescription('Created by the Wrapper-class. ' . time());
        $invoice->setClient($this->factr->clientsGet($client->getId()));
        $invoice->addItem($item);

        $invoice = $this->factr->invoicesCreate($invoice);
        $invoice->setDescription('Updated by the Wrapper-class. ' . time());
        $response = $this->factr->invoicesUpdate($invoice->getId(), $invoice);

        $this->assertTrue($response);

        // cleanup
        $this->factr->invoicesDelete($invoice->getId());
        $this->factr->clientsDelete($client->getId());
    }

    /**
     * Tests DeFactuur->invoicesDelete
     */
    public function testInvoicesDelete()
    {
        $address = $this->createAddress();

        $client = new \SumoCoders\DeFactuur\Client\Client();
        $client->setFirstName('Tijs');
        $client->setLastName('Verkoyen');
        $client->addEmail('php-factr@verkoyen.eu');
        $client->setBillingAddress($address);
        $client->setCompanyAddress($address);
        $client->setRemarks('Created by the Wrapper-class. ' . time());

        $client = $this->factr->clientsCreate($client);

        $item = new \SumoCoders\DeFactuur\Invoice\Item();
        $item->setDescription('just an item');
        $item->setPrice(123.45);
        $item->setAmount(67);
        $item->setVat(21);

        $invoice = new \SumoCoders\DeFactuur\Invoice\Invoice();
        $invoice->setDescription('Created by the Wrapper-class. ' . time());
        $invoice->setClient($this->factr->clientsGet($client->getId()));
        $invoice->addItem($item);

        $invoice = $this->factr->invoicesCreate($invoice);
        $response = $this->factr->invoicesDelete($invoice->getId());

        $this->assertTrue($response);

        // cleanup
        $this->factr->clientsDelete($client->getId());
    }

    /**
     * Tests DeFactuur->invoiceSendByMail
     */
    public function tesInvoiceSendByMail()
    {
        $address = $this->createAddress();

        $client = new \SumoCoders\DeFactuur\Client\Client();
        $client->setFirstName('Tijs');
        $client->setLastName('Verkoyen');
        $client->addEmail('php-factr@verkoyen.eu');
        $client->setBillingAddress($address);
        $client->setCompanyAddress($address);
        $client->setRemarks('Created by the Wrapper-class. ' . time());

        $response = $this->factr->clientsCreate($client);

        $item = new \SumoCoders\DeFactuur\Invoice\Item();
        $item->setDescription('just an item');
        $item->setPrice(123.45);
        $item->setAmount(67);
        $item->setVat(21);

        $invoice = new \SumoCoders\DeFactuur\Invoice\Invoice();
        $invoice->setDescription('Created by the Wrapper-class. ' . time());
        $invoice->setClient($this->factr->clientsGet($response->getId()));
        $invoice->addItem($item);

        $response = $this->factr->invoicesCreate($invoice);

        $response = $this->factr->invoiceSendByMail($response->getIid());
        $this->assertInstanceOf('\SumoCoders\DeFactuur\Invoice\Mail', $response);

        // cleanup
        $this->factr->invoicesDelete($invoice->getId());
        $this->factr->clientsDelete($client->getId());
    }

    /**
     * Tests DeFactuur->invoicesGet
     */
    public function testInvoicesGet()
    {
        $address = $this->createAddress();

        $client = new \SumoCoders\DeFactuur\Client\Client();
        $client->setFirstName('Tijs');
        $client->setLastName('Verkoyen');
        $client->addEmail('php-factr@verkoyen.eu');
        $client->setBillingAddress($address);
        $client->setCompanyAddress($address);
        $client->setRemarks('Created by the Wrapper-class. ' . time());

        $client = $this->factr->clientsCreate($client);

        $item = new \SumoCoders\DeFactuur\Invoice\Item();
        $item->setDescription('just an item');
        $item->setPrice(123.45);
        $item->setAmount(67);
        $item->setVat(21);

        $invoice = new \SumoCoders\DeFactuur\Invoice\Invoice();
        $invoice->setDescription('Created by the Wrapper-class. ' . time());
        $invoice->setClient($this->factr->clientsGet($client->getId()));
        $invoice->addItem($item);

        $invoice = $this->factr->invoicesCreate($invoice);

        $response = $this->factr->invoicesGet($invoice->getId());
        $this->assertInstanceOf('\SumoCoders\DeFactuur\Invoice\Invoice', $response);

        // cleanup
        $this->factr->invoicesDelete($invoice->getId());
        $this->factr->clientsDelete($client->getId());
    }

    /**
     * Tests DeFactuur->invoicesGet
     */
    public function testInvoicesGetByIid()
    {
        $address = $this->createAddress();

        $client = new \SumoCoders\DeFactuur\Client\Client();
        $client->setFirstName('Tijs');
        $client->setLastName('Verkoyen');
        $client->addEmail('php-factr@verkoyen.eu');
        $client->setBillingAddress($address);
        $client->setCompanyAddress($address);
        $client->setRemarks('Created by the Wrapper-class. ' . time());

        $client = $this->factr->clientsCreate($client);

        $item = new \SumoCoders\DeFactuur\Invoice\Item();
        $item->setDescription('just an item');
        $item->setPrice(123.45);
        $item->setAmount(67);
        $item->setVat(21);

        $invoice = new \SumoCoders\DeFactuur\Invoice\Invoice();
        $invoice->setDescription('Created by the Wrapper-class. ' . time());
        $invoice->setClient($this->factr->clientsGet($client->getId()));
        $invoice->addItem($item);

        $invoice = $this->factr->invoicesCreate($invoice);
        $this->factr->invoiceSendByMail($invoice->getId());
        $invoice = $this->factr->invoicesGet($invoice->getId());

        $response = $this->factr->invoicesGetByIid($invoice->getIid());
        $this->assertInstanceOf('\SumoCoders\DeFactuur\Invoice\Invoice', $response);

        // cleanup
        $this->factr->invoicesDelete($invoice->getId());
        $this->factr->clientsDelete($client->getId());
    }
}
