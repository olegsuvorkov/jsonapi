<?php

namespace JsonApi\Transformer;

use JsonApi\Metadata\MetadataInterface;

/**
 * @package JsonApi\Transformer
 */
class RelationshipTransformer implements TransformerInterface
{
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
    }
}
