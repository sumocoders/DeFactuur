<?php

namespace SumoCoders\DeFactuur\Peppol\Search;

class SearchResult
{
    // required
    protected string $id;

    protected string $name;

    protected SchemeValue $participantId;

    protected array $docTypes;

    protected array $entities;

    public function __construct(
        string $id,
        string $name,
        SchemeValue $participantId,
        array $docTypes,
        array $entities
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->participantId = $participantId;
        $this->docTypes = $docTypes;
        $this->entities = $entities;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParticipantId(): SchemeValue
    {
        return $this->participantId;
    }

    public function getDocTypes(): array
    {
        return $this->docTypes;
    }

    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * Initialize the object with raw data
     */
    public static function initializeWithRawData(array $data): SearchResult
    {
        $participantId = SchemeValue::initializeWithRawData($data['raw']['participantID']);
        $docTypes = [];
        foreach ($data['raw']['docTypes'] as $docType) {
            $docTypes[] = SchemeValue::initializeWithRawData($docType);
        }
        $entities = [];
        foreach ($data['raw']['entities'] as $entity) {
            $entities[] = Entity::initializeWithRawData($entity);
        }

        return new SearchResult(
            $data['id'],
            $data['name'],
            $participantId,
            $docTypes,
            $entities
        );
    }

    /**
     * Converts the object into an array
     */
    public function toArray(bool $forApi = false): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'participantId' => $this->participantId->toArray(),
            'docTypes' => array_map(function (SchemeValue $docType) {
                return $docType->toArray();
            }, $this->docTypes),
            'entities' => array_map(function (Entity $entity) use ($forApi) {
                return $entity->toArray();
            }, $this->entities),
        ];

        return $data;
    }
}
