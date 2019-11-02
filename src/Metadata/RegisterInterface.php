<?php

namespace JsonApi\Metadata;

/**
 * @package JsonApi\Metadata
 */
interface RegisterInterface extends \JsonSerializable
{
    /**
     * @param string|object $class
     * @return MetadataInterface
     * @throws UndefinedMetadataException
     */
    public function getByClass($class): MetadataInterface;

    /**
     * @param string $type
     * @return MetadataInterface
     * @throws UndefinedMetadataException
     */
    public function getByType(string $type): MetadataInterface;

    /**
     * @param string $type
     * @return bool
     */
    public function hasType(string $type): bool;
}
