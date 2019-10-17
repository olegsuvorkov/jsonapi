<?php

namespace JsonApi\Metadata;

use JsonApi\Transformer\InvalidArgumentException;

/**
 * @package JsonApi\ClassMetadata
 */
interface MetadataInterface
{
    /**
     * @return string
     */
    public function getClass(): string;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return MetadataInterface[]
     */
    public function getDiscrimination(): array;

    /**
     * @param $object
     * @return MetadataInterface
     */
    public function getOriginalMetadata($object): MetadataInterface;

    /**
     * @return FieldInterface[]
     */
    public function getIdentifiers(): array;

    /**
     * @return FieldInterface[]
     */
    public function getAttributes(): array;

    /**
     * @return FieldInterface[]
     */
    public function getRelationships(): array;

    /**
     * @param string $serializeName
     * @return FieldInterface[]
     */
    public function findRelationships(string $serializeName);

    /**
     * @param FieldInterface $field
     * @return bool
     */
    public function containsRelationship(FieldInterface $field): bool;

    /**
     * @param $object
     * @return string
     * @throws InvalidArgumentException
     */
    public function getId($object): string;

    /**
     * @return MetadataInterface|null
     */
    public function getParent(): ?MetadataInterface;

    /**
     * @param MetadataInterface|null $parent
     */
    public function setParent(?MetadataInterface $parent): void;

    /**
     * @param string[] $fields
     * @return MetadataInterface
     */
    public function createContextMetadata(?array $fields): MetadataInterface;

    /**
     * @param object|string $object
     * @return bool
     */
    public function isInstance($object): bool;
}
