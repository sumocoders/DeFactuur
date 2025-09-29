<?php

namespace SumoCoders\DeFactuur\Peppol\Search;

class Entity
{
    protected NameLanguage $name;
    protected string $countryCode;
    protected array $contacts;
    protected ?\DateTimeImmutable $registrationDate;

    protected ?string $geoInfo;

    protected array $identifiers;

    public function __construct(
        NameLanguage $name,
        string $countryCode,
        ?string $geoInfo,
        array $identifiers,
        array $contacts,
        ?\DateTimeImmutable $registrationDate
    ) {
        $this->name = $name;
        $this->countryCode = $countryCode;
        $this->geoInfo = $geoInfo;
        $this->identifiers = $identifiers;
        $this->contacts = $contacts;
        $this->registrationDate = $registrationDate;
    }

    public static function initializeWithRawData(array $data): Entity
    {
        $contacts = [];

        if (isset($data['contacts']) && is_array($data['contacts'])) {
            foreach ($data['contacts'] as $contactData) {
                $contact = Contact::initializeWithRawData($contactData);
                if ($contact !== null) {
                    $contacts[] = $contact;
                }
            }
        }

        $registrationDate = null;
        if (isset($data['regDate']) && $data['regDate'] !== '') {
            $registrationDate = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['regDate'] . ' 00:00:00');
        }

        $geoInfo = $data['geoInfo'] ?? null;

        $identifiers = [];
        if (isset($data['identifiers']) && is_array($data['identifiers'])) {
            foreach ($data['identifiers'] as $identifierData) {
                $identifiers[] = SchemeValue::initializeWithRawData($identifierData);
            }
        }

        return new Entity(
            new NameLanguage(
                $data['name'][0]['name'],
                isset($data['name'][0]['language']) && $data['name'][0]['language'] !== '' ? $data['name'][0]['language'] : null
            ),
            $data['countryCode'],
            $geoInfo,
            $identifiers,
            $contacts,
            $registrationDate,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'countryCode' => $this->countryCode,
            'geoInfo' => $this->geoInfo,
            'identifiers' => array_map(function (SchemeValue $identifier) {
                return $identifier->toArray();
            }, $this->identifiers),
            'contacts' => array_map(function (Contact $contact) {
                return $contact->toArray();
            }, $this->contacts),
            'registrationDate' => ($this->registrationDate) ? $this->registrationDate->format('Y-m-d') : null,
        ];
    }
}