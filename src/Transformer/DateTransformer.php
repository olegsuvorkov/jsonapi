<?php

namespace JsonApi\Transformer;

use DateTime;

/**
 * @package JsonApi\Transformer
 */
class DateTransformer implements TransformerInterface
{
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'date';
    }

    /**
     * @inheritDoc
     */
    public function transformScalar($data, array $options)
    {
        return $this->transform($data, $options) ?? '';
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
        } elseif (is_string($value)) {
            return $this->reverseTransform($value, $options);
        } else {
            throw new InvalidArgumentException();
        }
    }

    /**
     * @inheritDoc
     */
    public function transform($data, array $options)
    {
        if ($data instanceof DateTime) {
            return $data->format('Y-m-d');
        } elseif ($data === null) {
            return null;
        } else {
            throw new InvalidArgumentException();
        }
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($data, array $options)
    {
        if ($data === null) {
            return null;
        } elseif (is_string($data)) {
            return DateTime::createFromFormat('Y-m-d', $data);
        } else {
            throw new InvalidArgumentException();
        }
    }
}