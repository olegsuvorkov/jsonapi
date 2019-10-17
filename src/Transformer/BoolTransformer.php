<?php

namespace JsonApi\Transformer;

/**
 * @package JsonApi\Transformer
 */
class BoolTransformer extends Transformer
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
    public function transformScalar($data, array $options)
    {
        return parent::transformScalar($data, $options) ? 1 : 0;
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
}
