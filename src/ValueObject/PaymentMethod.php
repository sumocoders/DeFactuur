<?php

namespace SumoCoders\DeFactuur\ValueObject;

final class PaymentMethod
{
    private const NOT_PAID = 'not_paid';
    private const CHEQUE = 'cheque';
    private const TRANSFER = 'transfer';
    private const BANKCONTACT = 'bankcontact';
    private const CASH = 'cash';
    private const DIRECT_DEBIT = 'direct_debit';
    private const PAID = 'paid';

    private const ALLOWED_VALUES = [
        self::NOT_PAID,
        self::CHEQUE,
        self::TRANSFER,
        self::BANKCONTACT,
        self::CASH,
        self::DIRECT_DEBIT,
        self::PAID
    ];

    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;

        $this->validate();
    }

    private function validate(): void
    {
        if (!in_array($this->value, self::ALLOWED_VALUES)) {
            throw new InvalidArgumentException($this->value);
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function notPaid(): PaymentMethod
    {
        return new self(self::NOT_PAID);
    }

    public static function cheque(): PaymentMethod
    {
        return new self(self::CHEQUE);
    }

    public static function transfer(): PaymentMethod
    {
        return new self(self::TRANSFER);
    }

    public static function bankcontact(): PaymentMethod
    {
        return new self(self::BANKCONTACT);
    }

    public static function cash(): PaymentMethod
    {
        return new self(self::CASH);
    }

    public static function directDebit(): PaymentMethod
    {
        return new self(self::DIRECT_DEBIT);
    }

    public static function paid(): PaymentMethod
    {
        return new self(self::PAID);
    }
}
