<?php

namespace JsonApi\Transformer;

/**
 * @package JsonApi\Transformer
 */
class EnumTransformer extends Transformer
{
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'enum';
    }

    /**
     * @inheritDoc
     */
    public function transform($data, array $options)
    {
        if (in_array($data, $options['choices'])) {
            return $data;
        }
        throw new InvalidArgumentException('Expected `'.implode('`, `', $options['choices']).'`');
    }
}
