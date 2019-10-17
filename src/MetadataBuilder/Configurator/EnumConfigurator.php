<?php

namespace JsonApi\MetadataBuilder\Configurator;

use JsonApi\Metadata\Field;
use JsonApi\MetadataBuilder\BuilderException;

/**
 * @package JsonApi\MetadataBuilder\Configurator
 */
class EnumConfigurator extends Configurator
{
    /**
     * @inheritDoc
     */
    public function configure(Field $field, array $options, array $map): void
    {
        parent::configure($field, $options, $map);
        $choices = $options['choices'] ?? $this;
        unset($options['choices']);
        if ($choices === $this) {
            throw new BuilderException();
        }
        if (is_array($choices) && $choices) {
            $field->setOption('choices', array_values($choices));
        } else {
            throw new BuilderException();
        }
    }
}
