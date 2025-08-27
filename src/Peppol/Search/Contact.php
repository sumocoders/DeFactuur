<?php

namespace SumoCoders\DeFactuur\Peppol\Search;

class Contact
{
    protected ?string $type;
    protected ?string $name;
    protected ?string $phone;
    protected ?string $email;

    public function __construct(
        ?string $type = null,
        ?string $name = null,
        ?string $phone = null,
        ?string $email = null
    ) {
        $this->type = $type;
        $this->name = $name;
        $this->phone = $phone;
        $this->email = $email;
    }

    public static function initializeWithRawData(array $data)
    {
        $type = null;
        if ($data['type'] !== '') {
            $type = $data['type'];
        }
        $name = null;
        if ($data['name'] !== '' && $data['name'] !== 'x') {
            $name = $data['name'];
        }
        $phone = null;
        if ($data['phone'] !== '' && $data['phone'] !== 'x') {
            $phone = $data['phone'];
        }
        $email = null;
        if ($data['email'] !== '' && $data['email'] !== 'x') {
            $email = $data['email'];
        }

        if ($type === null && $name === null && $phone === null && $email === null) {
            return null;
        }

        return new Contact($type, $name, $phone, $email);
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
        ];
    }
}