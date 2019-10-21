<?php

namespace JsonApi\DataStorage;

use JsonApi\Metadata\MetadataInterface;

/**
 * @package JsonApi\DataStorage
 */
interface DataStorageInterface
{
    /**
     * @param MetadataInterface $metadata
     * @param string $id
     * @return object
     */
    public function get(MetadataInterface $metadata, string $id);

    /**
     * @param $object
     * @return bool
     */
    public function isNew($object): bool;

    /**
     * @param MetadataInterface $metadata
     * @param string $id
     * @param mixed $data
     * @return void
     */
    public function set(MetadataInterface $metadata, string $id, $data): void;
}
