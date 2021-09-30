<?php

namespace SumoCoders\DeFactuur\Client;

class Client
{
    // required
    protected string $lastName;

    protected array $email;

    protected Address $billingAddress;

    protected Address $companyAddress;

    // optional
    protected ?int $id;

    protected ?int $paymentDays;

    protected ?int $replacedById;

    protected ?string $cid;

    protected ?string $company;

    protected ?string $vat;

    protected ?string $firstName;

    protected ?string $phone;

    protected ?string $fax;

    protected ?string $cell;

    protected ?string $website;

    protected ?string $remarks;

    protected ?bool $invoiceableByEmail;

    protected ?bool $invoiceableBySnailMail;

    protected ?bool $invoiceableByFactr;

    protected ?bool $disabled;

    public function __construct(
        string $lastName,
        array $email,
        Address $billingAddress,
        Address $companyAddress
    ) {
        $this->lastName = $lastName;
        $this->email = $email;
        $this->billingAddress = $billingAddress;
        $this->companyAddress = $companyAddress;

        $this->id = null;
        $this->paymentDays = null;
        $this->replacedById = null;
        $this->cid = null;
        $this->company = null;
        $this->vat = null;
        $this->firstName = null;
        $this->phone = null;
        $this->fax = null;
        $this->cell = null;
        $this->website = null;
        $this->remarks = null;
        $this->invoiceableByEmail = null;
        $this->invoiceableBySnailMail = null;
        $this->invoiceableByFactr = null;
        $this->disabled = null;
    }

    public function setBillingAddress(Address $billingAddress): Client
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    public function getBillingAddress(): Address
    {
        return $this->billingAddress;
    }

    public function setCell(string $cell): Client
    {
        $this->cell = $cell;

        return $this;
    }

    public function getCell(): ?string
    {
        return $this->cell;
    }

    public function setCid(string $cid): Client
    {
        $this->cid = $cid;

        return $this;
    }

    public function getCid(): ?string
    {
        return $this->cid;
    }

    public function setCompany(string $company): Client
    {
        $this->company = $company;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompanyAddress(Address $companyAddress): void
    {
        $this->companyAddress = $companyAddress;
    }

    public function getCompanyAddress(): Address
    {
        return $this->companyAddress;
    }

    public function addEmail(string $email): Client
    {
        $this->email[] = $email;

        return $this;
    }

    public function setEmail(array $email): Client
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): array
    {
        return $this->email;
    }

    public function setFax(string $fax): Client
    {
        $this->fax = $fax;

        return $this;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setFirstName(string $firstName): Client
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    private function setId(int $id): Client
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setInvoiceableByEmail(bool $invoiceableByEmail): Client
    {
        $this->invoiceableByEmail = $invoiceableByEmail;

        return $this;
    }

    public function getInvoiceableByEmail(): ?bool
    {
        return $this->invoiceableByEmail;
    }

    public function setInvoiceableByFactr(bool $invoiceableByFactr): Client
    {
        $this->invoiceableByFactr = $invoiceableByFactr;

        return $this;
    }

    public function getInvoiceableByFactr(): ?bool
    {
        return $this->invoiceableByFactr;
    }

    public function setInvoiceableBySnailMail(bool $invoiceableBySnailMail): Client
    {
        $this->invoiceableBySnailMail = $invoiceableBySnailMail;

        return $this;
    }

    public function getInvoiceableBySnailMail(): ?bool
    {
        return $this->invoiceableBySnailMail;
    }

    public function setLastName(string $lastName): Client
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setPaymentDays(int $paymentDays): Client
    {
        $this->paymentDays = $paymentDays;

        return $this;
    }

    public function getPaymentDays(): ?int
    {
        return $this->paymentDays;
    }

    public function setPhone(string $phone): Client
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setRemarks(string $remarks): Client
    {
        $this->remarks = $remarks;

        return $this;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setVat(string $vat): Client
    {
        $this->vat = $vat;

        return $this;
    }

    public function getVat(): ?string
    {
        return $this->vat;
    }

    public function setWebsite(string $website): Client
    {
        $this->website = $website;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function getReplacedById(): ?int
    {
        return $this->replacedById;
    }

    public function setReplacedById(int $replacedById): Client
    {
        $this->replacedById = $replacedById;

        return $this;
    }

    public function isDisabled(): ?bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled = true): Client
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Initialize the object with raw data
     */
    public static function initializeWithRawData(array $data): Client
    {
        $item = new Client(
            $data['last_name'],
            $data['email'],
            Address::initializeWithRawData($data['billing_address']),
            Address::initializeWithRawData($data['company_address'])
        );

        if(isset($data['id'])) $item->setId($data['id']);
        if(isset($data['cid'])) $item->setCid($data['cid']);
        if(isset($data['first_name'])) $item->setFirstName($data['first_name']);
        if(isset($data['last_name'])) $item->setLastName($data['last_name']);
        if(isset($data['company'])) $item->setCompany($data['company']);
        if(isset($data['vat'])) $item->setVat($data['vat']);
        if (isset($data['company_address'])) {
            $address = Address::initializeWithRawData($data['company_address']);
            $item->setCompanyAddress($address);
        }
        if (isset($data['billing_address'])) {
            $address = Address::initializeWithRawData($data['billing_address']);
            $item->setBillingAddress($address);
        }
        if(isset($data['email'])) $item->setEmail($data['email']);
        if(isset($data['fax'])) $item->setFax($data['fax']);
        if(isset($data['phone'])) $item->setPhone($data['phone']);
        if(isset($data['cell'])) $item->setCell($data['cell']);
        if(isset($data['cellphone'])) $item->setCell($data['cellphone']);   // @remark: kinda stupid the API expects cell, but returns cellphone
        if(isset($data['website'])) $item->setWebsite($data['website']);
        if(isset($data['invoiceable_by_email'])) $item->setInvoiceableByEmail($data['invoiceable_by_email']);
        if(isset($data['invoiceable_by_snailmail'])) $item->setInvoiceableBySnailMail($data['invoiceable_by_snailmail']);
        if(isset($data['invoiceable_by_factr'])) $item->setInvoiceableByFactr($data['invoiceable_by_factr']);
        if(isset($data['payment_days'])) $item->setPaymentDays($data['payment_days']);
        if(isset($data['remarks'])) $item->setRemarks($data['remarks']);
        if(isset($data['replaced_by_id'])) $item->setReplacedById($data['replaced_by_id']);
        if(isset($data['disabled_at']) && !empty($data['disabled_at'])) $item->setDisabled();

        return $item;
    }

    /**
     * Converts the object into an array
     */
    public function toArray(bool $forApi = false): array
    {
        $data = [];

        $data['id'] = $this->getId();
        $data['cid'] = $this->getCid();
        $data['first_name'] = $this->getFirstName();
        $data['last_name'] = $this->getLastName();
        $data['company'] = $this->getCompany();
        $data['vat'] = $this->getVat();
        $address = $this->getCompanyAddress();
        if($address !== null) $data['company_address'] = $address->toArray($forApi);
        $address = $this->getBillingAddress();
        if($address !== null) $data['billing_address'] = $address->toArray($forApi);
        // @todo this is a bug in the API, so for now we just send the first email address
        $addresses = $this->getEmail();
        if(!empty($addresses)) $data['email'] = $addresses[0];
        $data['email'] = $addresses;
        $data['fax'] = $this->getFax();
        $data['phone'] = $this->getPhone();
        $data['cell'] = $this->getCell();
        $data['website'] = $this->getWebsite();
        $data['invoiceable_by_email'] = $this->getInvoiceableByEmail();
        $data['invoiceable_by_snailmail'] = $this->getInvoiceableBySnailMail();
        $data['invoiceable_by_factr'] = $this->getInvoiceableByFactr();
        $data['payment_days'] = $this->getPaymentDays();
        $data['remarks'] = $this->getRemarks();
        $data['replaced_by_id'] = $this->getReplacedById();

        return $data;
    }
}
