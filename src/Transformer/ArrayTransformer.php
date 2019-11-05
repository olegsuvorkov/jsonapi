<?php

namespace JsonApi\Transformer;

/**
 * @package JsonApi\Transformer
 */
class ArrayTransformer extends Transformer
{
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'array';
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
    public function transform($data, array $options)
    {
        if (is_array($data)) {
            return $data;
        }
        throw new InvalidArgumentException();
    }
}
