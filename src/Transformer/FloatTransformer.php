<?php

namespace JsonApi\Transformer;

/**
 * @package JsonApi\Transformer
 */
class FloatTransformer implements TransformerInterface
{
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'float';
    }

    /**
     * @inheritDoc
     */
    public function transform($data, array $options)
    {
        if (is_int($data) || is_float($data)) {
            return (float) $data;
        }
        throw new InvalidArgumentException('expected float');
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($data, array $options)
    {
    }
}
