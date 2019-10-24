<?php

namespace JsonApi\Transformer;

use Doctrine\Common\Persistence\ManagerRegistry;
use JsonApi\DataStorage\DataStorageInterface;
use JsonApi\Metadata\Metadata;
use JsonApi\Metadata\MetadataInterface;

/**
 * @package JsonApi\Transformer
 */
class RelationshipTransformer implements TransformerInterface
{
    /**
     * @var ManagerRegistry
     */
    private $registry;
    /**
     * @var DataStorageInterface
     */
    private $storage;

    public function __construct(DataStorageInterface $storage, ManagerRegistry $registry)
    {
        $this->registry = $registry;
        $this->storage = $storage;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'relationship';
    }

    /**
     * @inheritDoc
     */
    public function transformScalar($data, array $options)
    {
        return $options['target']->getOriginalMetadata($data)->getId($data);
    }

    /**
     * @inheritDoc
     */
    public function reverseTransformScalar(array &$ids, array $options)
    {
        /** @var MetadataInterface $metadata */
        $metadata = $options['target'];
        $class    = $metadata->getClass();
        $data     = $metadata->reverseTransformId($ids);
        return $this->registry->getManagerForClass($class)->find($class, $data);
    }

    /**
     * @inheritDoc
     */
    public function transform($data, array $options)
    {
        /** @var MetadataInterface $metadata */
        $metadata = $options['target']->getOriginalMetadata($data);
        return [
            'id'    => $metadata->getId($data),
            'type'  => $metadata->getType(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($data, array $options)
    {
        /** @var DataStorageInterface $storage */
        $storage = $options['storage'] ?? $this->storage;
        if (!is_array($data)) {
            throw new InvalidArgumentException();
        }
        if (!array_key_exists('data', $data)) {
            throw new InvalidArgumentException();
        }
        $data = $data['data'];
        if ($data === null) {
            return null;
        } elseif (is_array($data)) {
            [$id, $type] = Metadata::reverseRelatedTransform($data);
            /** @var MetadataInterface $metadata */
            $metadata = $options['target']->getMetadataByType($type);
            return $storage->get($metadata, $id);
        } else {
            throw new InvalidArgumentException();
        }
    }
}
