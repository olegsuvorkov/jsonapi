<?php

namespace JsonApi\Metadata;

/**
 * @package JsonApi\ClassMetadata
 */
interface MetadataInterface
{
    public function getClass(): string;

    public function getType(): ?string;

    public function setType(?string $type);

    /**
     * @return FieldInterface[]
     */
    public function getIdentifiers(): array;

    /**
     * @param FieldInterface $field
     * @return void
     */
    public function addIdentifier(FieldInterface $field): void;

    /**
     * @return mixed
     */
    public function getAttributes(): array;

    public function addAttribute(FieldInterface $field): void;

    /**
     * @return mixed
     */
    public function getRelationships(): array;

    /**
     * @param FieldInterface $field
     */
    public function addRelationship(FieldInterface $field): void;

    public function setDiscriminatorAttribute(string $attribute);

    public function addDiscriminator(string $value, MetadataInterface $metadata);

    public function addInherit(MetadataInterface $metadata);
}
