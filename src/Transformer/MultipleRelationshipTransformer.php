<?php

namespace JsonApi\Transformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JsonApi\DataStorage\DataStorageInterface;
use JsonApi\Metadata\Metadata;
use JsonApi\Metadata\MetadataInterface;

/**
 * @package JsonApi\Transformer
 */
class MultipleRelationshipTransformer implements TransformerInterface
{
    /**
     * @var TransformerInterface
     */
    private $relationshipTransformer;
    /**
     * @var DataStorageInterface
     */
    private $storage;

    /**
     * @param DataStorageInterface $storage
     * @param TransformerInterface $relationshipTransformer
     */
    public function __construct(DataStorageInterface $storage, TransformerInterface $relationshipTransformer)
    {
        $this->relationshipTransformer = $relationshipTransformer;
        $this->storage = $storage;
    }

    /**
     * @inheritDoc
     */
    public function transformScalar($data, array $options)
    {
        throw new InvalidArgumentException('Invalid type');
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'multiple_relationship';
    }

    /**
     * @inheritDoc
     */
    public function transform($data, array $options)
    {
        $result = [];
        if (is_iterable($data)) {
            foreach ($data as $item) {
                $result[] = $this->relationshipTransformer->transform($item, $options);
            }
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function reverseTransformScalar(array &$ids, array $options)
    {
        throw new InvalidArgumentException();
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
        $result = $options['data'] ?? new ArrayCollection();
        if (!$result instanceof Collection) {
            throw new InvalidArgumentException();
        }
        if (is_array($data)) {
            foreach ($data as $item) {
                [$id, $type] = Metadata::reverseRelatedTransform($item);
                /** @var MetadataInterface $metadata */
                $metadata = $options['target']->getMetadataByType($type);
                $result->add($storage->get($metadata, $id));
            }
        }
        return $result;
    }

    public function serializeOptions(array $options): array
    {
        return [
            'target' => $options['target']->getType(),
        ];
    }
}
