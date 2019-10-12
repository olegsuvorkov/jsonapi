<?php

namespace JsonApi\Transformer;

/**
 * @package JsonApi\Transformer
 */
class BoolTransformer implements TransformerInterface
{
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'bool';
    }

    /**
     * @inheritDoc
     */
    public function transform($data, array $options)
    {
        if (is_bool($data)) {
            return $data;
        }
        throw new InvalidArgumentException('Expected bool');
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($data, array $options)
    {
    }
}
