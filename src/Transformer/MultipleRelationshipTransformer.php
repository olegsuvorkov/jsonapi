<?php

namespace JsonApi\Transformer;

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
     * @param TransformerInterface $relationshipTransformer
     */
    public function __construct(TransformerInterface $relationshipTransformer)
    {
        $this->relationshipTransformer = $relationshipTransformer;
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
    public function reverseTransform($data, array $options)
    {
    }
}
