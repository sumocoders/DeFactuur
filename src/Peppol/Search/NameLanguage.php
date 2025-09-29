<?php

namespace SumoCoders\DeFactuur\Peppol\Search;

class NameLanguage
{
    protected string $name;
    protected ?string $language;

    public function __construct(string $name, ?string $language = null)
    {
        $this->name = $name;
        $this->language = $language;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public static function initializeWithRawData(array $data): NameLanguage
    {
        return new NameLanguage(
            $data['name'],
            $data['language'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'language' => $this->language,
        ];
    }
}