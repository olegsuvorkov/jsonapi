<?php

namespace JsonApi\Transformer;

/**
 * @package JsonApi\Transformer
 */
class FloatTransformer extends Transformer
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
}
