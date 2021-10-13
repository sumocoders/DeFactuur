<?php

namespace SumoCoders\DeFactuur\Invoice;

use SumoCoders\DeFactuur\Client\Client;
use DateTime;
use Exception;
use SumoCoders\DeFactuur\ValueObject\State;

/**
 * Class Invoice
 *
 * @package SumoCoders\DeFactuur\Invoice
 */
class Invoice
{
    protected ?Client $client;

    protected ?int $clientId;

    /** @var Item[] */
    protected array $items;

    protected ?int $id;

    protected ?string $iid;

    protected ?State $state;

    protected ?string $paymentMethod;

    protected ?string $description;

    protected ?string $shownRemark;

    protected ?DateTime $generated;

    protected ?DateTime $dueDate;

    protected ?DateTime $fullyPaidAt;

    /** @var Payment[] */
    protected ?array $payments;

    /** @var History[] */
    protected ?array $history;

    protected ?float $total;

    protected ?string $language;

    protected ?float $discount;

    protected bool $discountIsPercentage = false;

    protected ?string $discountDescription;

    protected ?string $vatException;

    protected ?string $vatDescription;

    protected bool $prepareForSending = false;

    public function __construct(
        ?Client $client = null,
        array $items = []
    ) {
        $this->client = $client;
        $this->clientId = $client === null ? null : $client->getId();
        $this->items = $items;

        $this->id = null;
        $this->iid = null;
        $this->state = null;
        $this->paymentMethod = null;
        $this->description = null;
        $this->shownRemark = null;
        $this->generated = null;
        $this->dueDate = null;
        $this->fullyPaidAt = null;
        $this->payments = null;
        $this->history = null;
        $this->total = null;
        $this->language = null;
        $this->discount = null;
        $this->discountDescription = null;
        $this->vatException = null;
        $this->vatDescription = null;
    }

    public function setClient(Client $client): Invoice
    {
        $this->client = $client;
        $this->clientId = $client->getId();

        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClientId(int $clientId): Invoice
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function setDescription(string $description): Invoice
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    private function setDueDate(DateTime $dueDate): void
    {
        $this->dueDate = $dueDate;
    }

    public function getDueDate(): ?DateTime
    {
        return $this->dueDate;
    }

    private function setFullyPaidAt(DateTime $fullyPaidAt): void
    {
        $this->fullyPaidAt = $fullyPaidAt;
    }

    public function getFullyPaidAt(): ?DateTime
    {
        return $this->fullyPaidAt;
    }

    private function setGenerated(DateTime $generated): void
    {
        $this->generated = $generated;
    }

    public function getGenerated(): ?DateTime
    {
        return $this->generated;
    }

    private function addHistory(History $history): void
    {
        $this->history[] = $history;
    }

    /**
     * @param History[] $history
     */
    public function setHistory(array $history): Invoice
    {
        $this->history = $history;

        return $this;
    }

    /**
     * @return History[]
     */
    public function getHistory(): ?array
    {
        return $this->history;
    }

    private function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    private function setIid(string $iid): void
    {
        $this->iid = $iid;
    }

    public function getIid(): ?string
    {
        return $this->iid;
    }

    public function addItem(Item $item): Invoice
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @param Item[] $items
     */
    public function setItems(array $items): Invoice
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Add a payment
     */
    private function addPayment(Payment $payment): void
    {
        $this->payments[] = $payment;
    }

    /**
     * @return Payment[]
     */
    public function getPayments(): ?array
    {
        return $this->payments;
    }

    public function setShownRemark(string $shownRemark): Invoice
    {
        $this->shownRemark = $shownRemark;

        return $this;
    }

    public function getShownRemark(): ?string
    {
        return $this->shownRemark;
    }

    public function setState(State $state): Invoice
    {
        $this->state = $state;

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setPaymentMethod(string $paymentMethod): Invoice
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    private function setTotal(float $total): void
    {
        $this->total = $total;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setLanguage(string $language): Invoice
    {
        $this->language = $language;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    public function setDiscount(float $discount): Invoice
    {
        $this->discount = $discount;

        return $this;
    }

    public function isDiscountAPercentage(): bool
    {
        return $this->discountIsPercentage;
    }

    public function setDiscountIsPercentage(bool $discountIsPercentage): Invoice
    {
        $this->discountIsPercentage = $discountIsPercentage;

        return $this;
    }

    public function getDiscountDescription(): ?string
    {
        return $this->discountDescription;
    }

    public function setDiscountDescription(string $discountDescription): Invoice
    {
        $this->discountDescription = $discountDescription;

        return $this;
    }

    public function prepareForSending(): void
    {
        $this->prepareForSending = true;
    }

    public function getVatException(): ?string
    {
        return $this->vatException;
    }

    public function setVatException(string $vatException): Invoice
    {
        $this->vatException = $vatException;

        return $this;
    }

    public function getVatDescription(): ?string
    {
        return $this->vatDescription;
    }

    public function setVatDescription(string $vatDescription): Invoice
    {
        $this->vatDescription = $vatDescription;

        return $this;
    }

    /**
     * Initialize the object with raw data
     *
     * @throws Exception
     */
    public static function initializeWithRawData(array $data): Invoice
    {
        $item = new Invoice();

        if(isset($data['id'])) $item->setId($data['id']);
        if(isset($data['client_id'])) $item->setClientId($data['client_id']);
        if(isset($data['iid'])) $item->setIid($data['iid']);
        if(isset($data['state'])) $item->setState(new State($data['state']));
        if(isset($data['payment_method'])) $item->setPaymentMethod($data['payment_method']);
        if(isset($data['generated'])) $item->setGenerated(new DateTime('@' . strtotime($data['generated'])));
        if(isset($data['description'])) $item->setDescription($data['description']);
        if(isset($data['shown_remark'])) $item->setShownRemark($data['shown_remark']);
        if (isset($data['items'])) {
            foreach ($data['items'] as $row) {
                $item->addItem(Item::initializeWithRawData($row));
            }
        }
        if (isset($data['payments'])) {
            foreach ($data['payments'] as $row) {
                $item->addPayment(Payment::initializeWithRawData($row));
            }
        }
        if(isset($data['total'])) $item->setTotal($data['total']);
        if(isset($data['discount'])) $item->setDiscount($data['discount']);
        if(isset($data['percentage'])) $item->setDiscountIsPercentage($data['percentage']);
        if(isset($data['discount_description'])) $item->setDiscountDescription($data['discount_description']);
        if(isset($data['due_date'])) $item->setDueDate(new DateTime('@' . strtotime($data['due_date'])));
        if(isset($data['fully_paid_at'])) $item->setFullyPaidAt(new DateTime('@' . strtotime($data['fully_paid_at'])));
        if (isset($data['history'])) {
            foreach ($data['history'] as $row) {
                $item->addHistory(History::initializeWithRawData($row));
            }
        }
        if (isset($data['language'])) {
            $item->setLanguage($data['language']);
        }

        return $item;
    }

    /**
     * Converts the object into an array
     */
    public function toArray(bool $forApi = false): array
    {
        $data = array();
        $data['client_id'] = $this->getClientId();
        $data['state'] = $this->getState()->getValue();
        $data['payment_method'] = $this->getPaymentMethod();
        $data['description'] = $this->getDescription();
        $data['shown_remark'] = $this->getShownRemark();
        $discount = $this->getDiscount();
        if (!empty($discount)) {
            $data['discount'] = $this->getDiscount();
            $data['percentage'] = $this->isDiscountAPercentage();
            $data['discount_description'] = $this->getDiscountDescription();
        }
        if ($this->getVatException()) {
            $data['vat_exception'] = $this->getVatException();
        }
        if ($this->getVatDescription()) {
            $data['vat_description'] = $this->getVatDescription();
        }

        $data['items'] = array();
        foreach ($this->getItems() as $item) {
            $data['items'][] = $item->toArray($forApi);
        }
        $data['language'] = $this->getLanguage();

        if (!$forApi) {
            $data['id'] = $this->getId();
            $data['idd'] = $this->getIid();
            $data['generated'] = $this->getGenerated()->getTimestamp();
            $data['due_date'] = $this->getDueDate()->getTimestamp();
            $data['payments'] = array();
            foreach ($this->getPayments() as $payment) {
                $data['payments'][] = $payment->toArray($forApi);
            }
            $data['total'] = $this->getTotal();
            $data['client'] = $this->getClient()->toArray($forApi);
            $data['history'] = array();
            foreach ($this->getHistory() as $history) {
                $data['history'][] = $history->toArray($forApi);
            }
        } elseif ($this->prepareForSending) {
            $data['prepare_for_sending'] = $this->prepareForSending;
        }

        return $data;
    }
}
