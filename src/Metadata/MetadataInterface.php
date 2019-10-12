<?php

namespace JsonApi\Metadata;

use JsonApi\Transformer\InvalidArgumentException;
use JsonApi\Transformer\TransformerPool;

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
     * @param $object
     * @return string
     */
    public function getTypeByObject($object): string;

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
     * @param $object
     * @param TransformerPool $pool
     * @return string
     * @throws InvalidArgumentException
     */
    public function getId($object, TransformerPool $pool): string;

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
     * @param object $object
     * @return bool
     */
    public function isInstance($object): bool;
}
