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
    public function reverseTransformScalar(array &$ids, array $options)
    {
        $value = current($ids);
        if ($value === false) {
            throw new InvalidArgumentException();
        }
        next($ids);
        if ($value === '') {
            return null;
        } elseif (in_array($value, $options['choices'])) {
            return $value;
        } else {
            throw new InvalidArgumentException('Expected `'.implode('`, `', $options['choices']).'`');
        }
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

    /**
     * @inheritDoc
     */
    public function serializeOptions(array $options): array
    {
        return [
            'type' => $this->getType(),
            'choices' => $options['choices'],
        ];
    }
}
