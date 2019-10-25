<?php

namespace JsonApi\DataStorage;

use JsonApi\Metadata\MetadataInterface;
use JsonApi\Transformer\InvalidArgumentException;

/**
 * @package JsonApi\DataStorage
 */
class DataStorage implements DataStorageInterface
{
    /**
     * @inheritDoc
     */
    public function get(MetadataInterface $metadata, string $id)
    {
        $item = $metadata->find($id);
        if ($item) {
            return $item;
        }
        throw new InvalidArgumentException();
    }

    /**
     * @inheritDoc
     */
    public function isNew($object): bool
    {
        return false;
    }
}
