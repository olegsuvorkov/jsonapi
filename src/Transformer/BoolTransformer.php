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
    public function reverseTransformScalar(array &$ids, array $options)
    {
        $value = current($ids);
        if ($value === false) {
            throw new InvalidArgumentException();
        }
        next($ids);
        if ($value === '') {
            return null;
        } elseif ($value === '1') {
            return true;
        } elseif ($value === '0') {
            return false;
        } else {
            throw new InvalidArgumentException('Expected bool');
        }
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
