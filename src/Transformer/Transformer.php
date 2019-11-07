<?php

namespace JsonApi\Transformer;

/**
 * @package JsonApi\Transformer
 */
abstract class Transformer implements TransformerInterface
{
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
    public function reverseTransform($data, array $options)
    {
        return $this->transform($data, $options);
    }

    /**
     * @inheritDoc
     */
    public function serializeOptions(array $options): array
    {
        return [
            'type' => $this->getType(),
        ];
    }
}
