<?php

namespace JsonApi\Transformer;

/**
 * @package JsonApi\Transformer
 */
class IntegerTransformer implements TransformerInterface
{
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'int';
    }

    /**
     * @inheritDoc
     */
    public function transform($data, array $options)
    {
        if (is_int($data)) {
            return $data;
        }
        throw new InvalidArgumentException('Invalid type');
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($data, array $options)
    {
    }
}
