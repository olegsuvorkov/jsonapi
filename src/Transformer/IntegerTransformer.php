<?php

namespace JsonApi\Transformer;

/**
 * @package JsonApi\Transformer
 */
class IntegerTransformer extends Transformer
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
}
