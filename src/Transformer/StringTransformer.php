<?php

namespace JsonApi\Transformer;

/**
 * @package JsonApi\Transformer
 */
class StringTransformer extends Transformer
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
    public function reverseTransformScalar(array &$ids, array $options)
    {
        $value = current($ids);
        if ($value === false) {
            throw new InvalidArgumentException();
        }
        next($ids);
        if ($value === '') {
            return null;
        } else {
            return $value;
        }
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
}
