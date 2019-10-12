<?php

namespace JsonApi\Transformer;

/**
 * @package JsonApi\Transformer
 */
class StringTransformer implements TransformerInterface
{
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'string';
    }

    /**
     * @inheritDoc
     */
    public function transform($data, array $options)
    {
        if (is_string($data)) {
            return $data;
        }
        throw new InvalidArgumentException();
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($data, array $options)
    {
    }
}
