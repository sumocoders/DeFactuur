<?php

namespace SumoCoders\DeFactuur\Peppol\Search;

class SchemeValue
{
    protected string $scheme;
    protected $value;

    public function __construct(string $scheme, string $value)
    {
        $this->scheme = $scheme;
        $this->value = $value;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getValue()
    {
        return $this->value;
    }

    public static function initializeWithRawData(array $data): SchemeValue
    {
        return new SchemeValue(
            $data['scheme'],
            $data['value']
        );
    }

    public function toArray(): array
    {
        return [
            'scheme' => $this->scheme,
            'value' => $this->value,
        ];
    }
}