<?php

namespace JsonApi\Metadata;

/**
 * @package JsonApi\Metadata
 */
interface RegisterInterface
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
}
