<?php

namespace JsonApi\Metadata;

/**
 * @package JsonApi\Metadata
 */
interface RegisterInterface
{
    /**
     * @param MetadataInterface $metadata
     */
    public function add(MetadataInterface $metadata): void;

    /**
     * @param string $class
     * @return MetadataInterface
     * @throws UndefinedMetadataException
     */
    public function getByClass(string $class): MetadataInterface;

    /**
     * @param string $type
     * @return MetadataInterface
     * @throws UndefinedMetadataException
     */
    public function getByType(string $type): MetadataInterface;

    /**
     * @return Metadata[]
     */
    public function all(): array;
}
