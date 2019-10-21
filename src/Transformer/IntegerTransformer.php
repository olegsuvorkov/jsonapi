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
            return intval($value);
        }
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
