<?php

namespace JsonApi\FieldNormalizer;

use JsonApi\Exception\LoaderException;
use JsonApi\Metadata\FieldInterface;

/**
 * @package JsonApi\FieldNormalizer
 */
class EnumFieldNormalizer implements FieldNormalizerInterface, ConfigureFieldNormalizerInterface
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
    public function normalize(FieldInterface $field, $data)
    {
        // TODO: Implement normalize() method.
    }

    /**
     * @inheritDoc
     */
    public function denormalize(FieldInterface $field, $data)
    {
        // TODO: Implement denormalize() method.
    }

    /**
     * @inheritDoc
     */
    public function configureAttribute(FieldInterface $field, array &$parameters): void
    {
        $choices = $parameters['choices'] ?? $this;
        unset($parameters['choices']);
        if ($choices === $this) {
            throw new LoaderException();
        }
        if (is_array($choices) && $choices) {
            $field->setType($this->getType());
            $field->setOption('choices', array_values($choices));
        } else {
            throw new LoaderException();
        }
    }
}
