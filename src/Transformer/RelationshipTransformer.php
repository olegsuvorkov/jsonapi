<?php

namespace JsonApi\Transformer;

use JsonApi\Metadata\MetadataInterface;

/**
 * @package JsonApi\Transformer
 */
class RelationshipTransformer implements TransformerInterface
{
    /**
     * @var TransformerPool
     */
    private $pool;

    public function __construct(TransformerPool $pool)
    {
        $this->pool = $pool;
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
    public function transform($data, array $options)
    {
        /** @var MetadataInterface $metadata */
        $metadata = $options['target'];
        return [
            'id'    => $metadata->getId($data, $this->pool),
            'type'  => $metadata->getTypeByObject($data),
        ];
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($data, array $options)
    {
    }
}
