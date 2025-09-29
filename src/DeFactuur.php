<?php

namespace SumoCoders\DeFactuur;

use Exception;
use InvalidArgumentException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use SumoCoders\DeFactuur\Client\Client;
use SumoCoders\DeFactuur\Exception as DeFactuurException;
use SumoCoders\DeFactuur\Invoice\Invoice;
use SumoCoders\DeFactuur\Invoice\Mail;
use SumoCoders\DeFactuur\Invoice\Payment;
use SumoCoders\DeFactuur\Peppol\Search\SearchResult;
use SumoCoders\DeFactuur\Product\Product;

class DeFactuur
{
    // internal constant to enable/disable debugging
    const DEBUG = false;

    // url for the API
    const API_URL = 'https://app.defactuur.be/api';

    // port for the factr-API
    const API_PORT = 443;

    // version of the API
    const API_VERSION = 'v1';

    // current version
    const VERSION = '2.5.1';

    /**
     * The token to use
     */
    private string $apiToken;

    private ClientInterface $client;

    private RequestFactoryInterface $requestFactory;

    private StreamFactoryInterface $streamFactory;

    /**
     * The timeout
     */
    private int $timeOut = 30;

    /**
     * The user agent
     */
    private string $userAgent;

// class methods
    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        ?string $apiToken = null
    ) {
        $this->client = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;

        if ($apiToken !== null && $apiToken !== '') {
            $this->apiToken = $apiToken;
        }
    }

    /**
     * Decode the response
     *
     * @param mixed $value
     */
    private function decodeResponse(&$value, string $key)
    {
        // convert to float
        if (
            in_array(
                $key,
                array('amount', 'price', 'total_without_vat', 'total_with_vat', 'total_vat', 'total'),
                true
            )
        ) {
            $value = (float) $value;
        }
    }

    /**
     * Encode data for usage in the API
     *
     * @param mixed $data
     */
    private function encodeData($data, array $array = [], ?string $prefix = null): array
    {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            $k = isset($prefix) ? $prefix . '[' . $key . ']' : $key;
            if (is_array($value) || is_object($value)) {
                $array = $this->encodeData($value, $array, $k);
            } else {
                $array[$k] = $value;
            }
        }

        return $array;
    }

    private function doCallAndReturnStatusCode(
        string $url,
        ?array $parameters = null,
        string $method = 'GET'
    ): int {
        return $this->doCall($url, $parameters, $method)->getStatusCode();
    }

    /**
     * Make the call
     *
     * @return array|bool|string
     */
    private function doCallAndReturnData(
        string $url,
        ?array $parameters = null,
        string $method = 'GET'
    ) {
        $response = $this->doCall($url, $parameters, $method);

        if (stristr($url, '.pdf')) {
            // Return pdf contents immediately without tampering with them
            return $response->getBody()->getContents();
        }

        $json = json_decode($response->getBody()->getContents(), true);

        // validate json
        if ($json === false) {
            throw new DeFactuurException('Invalid JSON-response');
        }

        // decode the response
        array_walk_recursive($json, array(__CLASS__, 'decodeResponse'));

        // return
        return $json;
    }

    /**
     * Make the call
     *
     * @return array|bool|string|int
     * @throws DeFactuurException
     */
    private function doCall(
        string $url,
        ?array $parameters = null,
        string $method = 'GET'
    ): ResponseInterface {
        $data = null;

        // add credentials
        $parameters['api_key'] = $this->getApiToken();

        // through GET
        if ($method === 'GET') {
            // build url
            $url .= '?' . http_build_query($parameters);
            $url = $this->removeIndexFromArrayParameters($url);
        } elseif ($method === 'POST') {
            $data = $this->encodeData($parameters);

            if ($this->areWeSendingAFile($data) === false) {
                $data = http_build_query($data);
                $data = $this->removeIndexFromArrayParameters($data);
            }
        } elseif ($method === 'DELETE') {
            // build url
            $url .= '?' . http_build_query($parameters, null);
        } elseif ($method === 'PUT') {
            $data = $this->encodeData($parameters);
            $data = http_build_query($data, null);
            $data = $this->removeIndexFromArrayParameters($data);
        } else {
            throw new DeFactuurException('Unsupported method (' . $method . ')');
        }

        // prepend
        $url = self::API_URL . '/' . self::API_VERSION . '/' . $url;
        $request = $this->requestFactory->createRequest($method, $url);

        if ($data !== null) {
            $request = $request->withBody($this->streamFactory->createStream($data));
        }

        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new DeFactuurException($e->getMessage());
        }

        if ($response->getStatusCode() === 422) {
            // Unprocessable entity = validation error on data that was passed
            throw new DeFactuurException(
                'Validation error - Unprocessable entity. (' . $response->getStatusCode() . ')',
                $response->getStatusCode()
            );
        }

        if ($response->getStatusCode() >= 400) {
            try {
                $json = json_decode($response->getBody()->getContents(), true);

                // throw
                if ($json !== null && $json !== false) {
                    // errors?
                    if (is_array($json) && array_key_exists('errors', $json)) {
                        $message = '';
                        foreach ($json['errors'] as $key => $value) {
                            $message .= $key . ': ' . implode(', ', $value) . "\n";
                        }

                        throw new DeFactuurException(trim($message));
                    } else {
                        if (is_array($json) && array_key_exists('message', $json)) {
                            $message = $json['message'];
                        }
                        throw new DeFactuurException($message ?? 'No message', $response->getStatusCode());
                    }
                }
            } catch (\Exception $e) {
                throw new DeFactuurException($e->getMessage());
            }

            // unknown error
            throw new DeFactuurException(
                'Invalid response (' . $response->getStatusCode() . ')',
                $response->getStatusCode()
            );
        }

        return $response;
    }

    /**
     * Detect from flattened parameter array if we're sending a file
     */
    private function areWeSendingAFile(array $parameters): bool
    {
        foreach ($parameters as $value) {
            if (substr($value, 0, 1) === '@') {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove indexes from http array parameters
     *
     * The DeFactuur application doesn't like numerical indexes in http parameters too much.
     * We'll just remove them.
     *
     * ?foo[1]=bar becomes ?foo[]=bar
     *
     * @param mixed $query The query string or flattened array
     *
     * @return array|null|string|string[] the cleaned up query string or array
     */
    private function removeIndexFromArrayParameters($query)
    {
        return preg_replace('/%5B([0-9]*)%5D/iU', '%5B%5D', $query);
    }

    /**
     * Get the API token
     */
    public function getApiToken(): string
    {
        return $this->apiToken;
    }

    /**
     * Get the timeout that will be used
     */
    public function getTimeOut(): int
    {
        return $this->timeOut;
    }

    /**
     * Get the user agent that will be used. Our version will be prepended to yours.
     * It will look like: "PHP DeFactuur/<version> <your-user-agent>"
     */
    public function getUserAgent(): string
    {
        return 'PHP DeFactuur/' . self::VERSION . ' ' . $this->userAgent;
    }

    /**
     * Set the API token
     */
    public function setApiToken(string $apiToken): void
    {
        $this->apiToken = $apiToken;
    }

    /**
     * Set the timeout
     * After this time the request will stop. You should handle any errors triggered by this.
     */
    public function setTimeOut(int $seconds): void
    {
        $this->timeOut = $seconds;
    }

    /**
     * Set the user-agent for you application
     * It will be appended to ours, the result will look like: "PHP DeFactuur/<version> <your-user-agent>"
     *
     * @param string $userAgent Your user-agent, it should look like <app-name>/<app-version>.
     */
    public function setUserAgent(string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }

// account methods

    /**
     * Get an API token
     *
     * @throws DeFactuurException
     */
    public function accountApiToken(string $username, string $password): string
    {
        $url = self::API_URL . '/' . self::API_VERSION . '/account/api_token.json';

        $request = $this->requestFactory->createRequest('GET', $url);
        $request = $request->withAddedHeader(
            'Authorization',
            'Basic ' . base64_encode($username . ':' . $password)
        );

        try {
            $response = $this->client->sendRequest($request);

            // check status code
            if ($response->getStatusCode() != 200) {
                throw new DeFactuurException('Could\'t authenticate you');
            }

            // we expect JSON so decode it
            $json = json_decode($response->getBody()->getContents(), true);
        } catch (ClientExceptionInterface $e) {
            throw new DeFactuurException($e->getMessage());
        }

        // validate json
        if ($json === false || !isset($json['api_token'])) {
            throw new DeFactuurException('Invalid JSON-response');
        }

        // return
        return $json['api_token'];
    }

// client methods

    /**
     * Get a list of all the clients for the authenticating user.
     *
     * @throws DeFactuurException
     */
    public function clients(): array
    {
        $clients = array();
        $rawData = $this->doCallAndReturnData('clients.json');
        if (!empty($rawData)) {
            foreach ($rawData as $data) {
                $clients[] = Client::initializeWithRawData($data);
            }
        }

        return $clients;
    }

    /**
     * Get all of the available information for a single client. You 'll need the id of the client.
     *
     * @return Client|bool
     * @throws DeFactuurException
     */
    public function clientsGet(string $id)
    {
        $rawData = $this->doCallAndReturnData('clients/' . $id . '.json');
        if (empty($rawData)) {
            return false;
        }

        return Client::initializeWithRawData($rawData);
    }

    /**
     * Get all clients that are linked to an email address
     *
     * @return Client[]|bool
     * @throws DeFactuurException
     */
    public function clientsGetByEmail(string $email)
    {
        $rawData = $this->doCallAndReturnData('clients.json', array('email' => $email));
        if (empty($rawData)) {
            return false;
        }

        return array_map(
            function (array $clientData) {
                return Client::initializeWithRawData($clientData);
            },
            $rawData
        );
    }

    /**
     * Create a new client.
     *
     * @throws DeFactuurException
     */
    public function clientsCreate(Client $client): Client
    {
        $parameters['client'] = $client->toArray(true);
        $rawData = $this->doCallAndReturnData('clients.json', $parameters, 'POST');

        return Client::initializeWithRawData($rawData);
    }

    /**
     * Update an existing client
     *
     * @throws DeFactuurException
     */
    public function clientsUpdate(string $id, Client $client): bool
    {
        $parameters['client'] = $client->toArray(true);
        $rawData = $this->doCallAndReturnStatusCode('clients/' . $id . '.json', $parameters, 'PUT', true);

        return $rawData === 204;
    }

    /**
     * Check if country is European
     *
     * @throws DeFactuurException
     */
    public function clientsIsEuropean(string $countryCode): bool
    {
        $parameters['country_code'] = $countryCode;
        $rawData = $this->doCallAndReturnData('clients/is_european.json', $parameters);

        return $rawData['european'];
    }

    /**
     * Delete a client
     *
     * @throws DeFactuurException
     */
    public function clientsDelete(string $id): bool
    {
        $rawData = $this->doCallAndReturnStatusCode('clients/' . $id . '.json', null, 'DELETE', true);

        return $rawData === 204;
    }

    /**
     * Disable client in favour of another client
     *
     * @throws DeFactuurException
     */
    public function clientsDisable(string $id, string $replacedById): bool
    {
        $parameters['replaced_by_id'] = $replacedById;
        $rawData = $this->doCallAndReturnStatusCode('clients/' . $id . '/disable.json', $parameters, 'POST', true);

        return $rawData === 201;
    }

    /**
     * Get the invoices for a client
     *
     * @throws Exception
     */
    public function clientsInvoices(int $id): array
    {
        $invoices = array();
        $rawData = $this->doCallAndReturnData('clients/' . $id . '/invoices.json');
        if (!empty($rawData)) {
            foreach ($rawData as $data) {
                $invoices[] = Invoice::initializeWithRawData($data);
            }
        }

        return $invoices;
    }

    // PEPPOL methods

    public function peppolSearch(string $searchTerm): array
    {
        $parameters['q'] = $searchTerm;
        $results = array();
        $rawData = $this->doCallAndReturnData('peppol/search', $parameters);

        if (!empty($rawData)) {
            foreach ($rawData as $data) {
                $results[] = SearchResult::initializeWithRawData($data);
            }
        }

        return $results;
    }

// invoice methods

    /**
     * Get a list of all the invoices.
     *
     * @throws Exception
     */
    public function invoices(?array $filters = null): array
    {
        $parameters = null;

        if (!empty($filters)) {
            $allowedFilters = array(
                'sent',
                'unpaid',
                'paid',
                'reminder_sent',
                'partially_paid',
                'unset',
                'juridicial_proceedings',
                'late',
            );

            array_walk(
                $filters,
                function ($filter) use ($allowedFilters) {
                    if (!in_array($filter, $allowedFilters)) {
                        throw new InvalidArgumentException('Invalid filter');
                    }
                }
            );

            $parameters = array('filters' => $filters);
        }

        $invoices = array();
        $rawData = $this->doCallAndReturnData('invoices.json', $parameters);
        if (!empty($rawData)) {
            foreach ($rawData as $data) {
                $invoices[] = Invoice::initializeWithRawData($data);
            }
        }

        return $invoices;
    }

    /**
     * Get all of the available information for a single invoice. You 'll need the id of the invoice.
     *
     * @return Invoice|bool
     * @throws Exception
     */
    public function invoicesGet(string $id)
    {
        $rawData = $this->doCallAndReturnData('invoices/' . $id . '.json');
        if (empty($rawData)) {
            return false;
        }

        return Invoice::initializeWithRawData($rawData);
    }

    /**
     * Get the pdf for an invoice
     *
     * @return string|bool Raw PDF contents
     * @throws DeFactuurException
     */
    public function invoicesGetAsPdf(string $id)
    {
        $rawData = $this->doCallAndReturnData('invoices/' . $id . '.pdf');

        if (empty($rawData)) {
            return false;
        }

        return $rawData;
    }

    /**
     * Get all of the available information for a single invoice. You 'll need the iid of the invoice.
     *
     * @return Invoice|bool
     * @throws Exception
     */
    public function invoicesGetByIid(string $iid)
    {
        $rawData = $this->doCallAndReturnData('invoices/by_iid/' . $iid . '.json');
        if (empty($rawData)) {
            return false;
        }

        return Invoice::initializeWithRawData($rawData);
    }

    /**
     * Create a new invoice.
     *
     * @throws Exception
     */
    public function invoicesCreate(Invoice $invoice): Invoice
    {
        if (($invoice->getVatException() && !$invoice->getVatDescription())
            || (!$invoice->getVatException() && $invoice->getVatDescription())) {
            throw new InvalidArgumentException('Vat exception and vat description are required if one of them is filled');
        }

        $parameters['invoice'] = $invoice->toArray(true);
        $rawData = $this->doCallAndReturnData('invoices.json', $parameters, 'POST');

        return Invoice::initializeWithRawData($rawData);
    }

    /**
     * Create a new credit note on invoice.
     *
     * @throws Exception
     */
    public function invoicesCreateCreditNote(string $id, Invoice $creditNote): Invoice
    {
        $parameters['credit_note'] = $creditNote->toArray(true);
        $rawData = $this->doCallAndReturnData('invoices/' . $id . '/credit_notes.json', $parameters, 'POST');

        return Invoice::initializeWithRawData($rawData);
    }

    /**
     * Update an existing invoice
     *
     * @throws DeFactuurException
     */
    public function invoicesUpdate(string $id, Invoice $invoice): bool
    {
        $parameters['invoice'] = $invoice->toArray(true);
        $rawData = $this->doCallAndReturnStatusCode('invoices/' . $id . '.json', $parameters, 'PUT', true);

        return $rawData === 204;
    }

    /**
     * Delete an invoice
     *
     * @throws DeFactuurException
     */
    public function invoicesDelete(int $id): bool
    {
        $rawData = $this->doCallAndReturnStatusCode('invoices/' . $id . '.json', null, 'DELETE', true);

        return $rawData === 204;
    }

    /**
     * Sending an invoice by mail.
     *
     * @return Mail|bool
     * @throws DeFactuurException
     */
    public function invoiceSendByMail(
        string $id,
        ?string $to = null,
        ?string $cc = null,
        ?string $bcc = null,
        ?string $subject = null,
        ?string $text = null
    ) {
        $parameters = array();
        if ($to !== null) {
            $parameters['mail']['to'] = $to;
        }
        if ($cc !== null) {
            $parameters['mail']['cc'] = $cc;
        }
        if ($bcc !== null) {
            $parameters['mail']['bcc'] = $bcc;
        }
        if ($subject !== null) {
            $parameters['mail']['subject'] = $subject;
        }
        if ($text !== null) {
            $parameters['mail']['text'] = $text;
        }
        $rawData = $this->doCallAndReturnData('invoices/' . $id . '/mails.json', $parameters, 'POST');
        if (empty($rawData)) {
            return false;
        }

        return Mail::initializeWithRawData($rawData);
    }

    /**
     * Marking invoice as sent by mail.
     *
     * @throws DeFactuurException
     */
    public function invoiceMarkAsSentByMail(string $id, string $email): void
    {
        $parameters = array();
        $parameters['by'] = 'mail';
        $parameters['to'] = $email;
        $this->doCallAndReturnData('invoices/' . $id . '/sent', $parameters, 'POST');
    }

    /**
     * Adding a payment to an invoice.
     *
     * @throws Exception
     */
    public function invoicesAddPayment(string $id, Payment $payment): Payment
    {
        $parameters['payment'] = $payment->toArray(true);
        $rawData = $this->doCallAndReturnData('invoices/' . $id . '/payments.json', $parameters, 'POST');

        return Payment::initializeWithRawData($rawData);
    }

    /**
     * Send a reminder for an invoice
     *
     * @param string $id The invoice id
     *
     * @throws DeFactuurException
     */
    public function invoiceSendReminder(string $id): array
    {
        return $this->doCallAndReturnData('invoices/' . $id . '/reminders', array(), 'POST');
    }

    /**
     * Check if vat is required
     *
     * @throws DeFactuurException
     */
    public function invoicesVatRequired(string $countryCode, bool $isCompany): bool
    {
        $parameters['country_code'] = $countryCode;
        $parameters['company'] = $isCompany;
        $rawData = $this->doCallAndReturnData('invoices/vat_required.json', $parameters);

        return $rawData['vat_required'];
    }

    /**
     * Check if valid vat number
     *
     * @throws DeFactuurException
     */
    public function isValidVat(string $vatNumber): bool
    {
        $parameters['vat'] = $vatNumber;
        $rawData = $this->doCallAndReturnData('vat/verify.json', $parameters);

        return $rawData['valid'];
    }

    /**
     * Upload a CODA file and let DeFactuur interpret it
     *
     * @throws DeFactuurException
     */
    public function uploadCodaFile(string $filePath): array
    {
        $parameters = array(
            'file' => '@' . $filePath,
            'file_type' => 'coda',
        );

        return $this->doCallAndReturnData('payments/process_file.json', $parameters, 'POST');
    }

    /**
     * @throws DeFactuurException
     */
    public function products(): array
    {
        $products = array();
        $rawData = $this->doCallAndReturnData('products.json');

        if (!empty($rawData)) {
            foreach ($rawData as $data) {
                $products[] = Product::initializeWithRawData($data);
            }
        }

        return $products;
    }

    /**
     * @return Product|bool
     * @throws DeFactuurException
     */
    public function productsGet(int $id)
    {
        $rawData = $this->doCallAndReturnData('products/' . $id . '.json');

        if (empty($rawData)) {
            return false;
        }

        return Product::initializeWithRawData($rawData);
    }

    /**
     * @throws DeFactuurException
     */
    public function productsCreate(Product $product): Product
    {
        $parameters['product'] = $product->toArray();

        $rawData = $this->doCallAndReturnData('products.json', $parameters, 'POST');

        return Product::initializeWithRawData($rawData);
    }
}
