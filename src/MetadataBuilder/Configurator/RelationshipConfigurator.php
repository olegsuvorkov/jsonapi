<?php

namespace JsonApi\MetadataBuilder\Configurator;

use JsonApi\Metadata\Field;
use JsonApi\MetadataBuilder\BuilderException;
use JsonApi\MetadataBuilder\MetadataBuilder;
use JsonApi\Transformer\TransformerInterface;

/**
 * @package JsonApi\MetadataBuilder\Configurator
 */
class RelationshipConfigurator implements ConfiguratorInterface
{
    /**
     * @var TransformerInterface
     */
    private $single;

    /**
     * @var TransformerInterface
     */
    private $multiple;

    /**
     * @param TransformerInterface $single
     * @param TransformerInterface $multiple
     */
    public function __construct(TransformerInterface $single, TransformerInterface $multiple)
    {
        $this->single = $single;
        $this->multiple = $multiple;
    }

    /**
     * @inheritDoc
     */
    public function configure(Field $field, array $options, array $map): void
    {
        $multiple = false;
        $type = null;
        foreach ($options as $key => $value) {
            if ($key === 'multiple') {
                if (is_bool($value)) {
                    $multiple = $value;
                } else {
                    throw new BuilderException('Invalid property multiple expected bool');
                }
            } elseif ($key === 'type') {
                if (is_string($value)) {
                    $type = $value;
                } else {
                    throw new BuilderException('Invalid property type expected string');
                }
            } else {
                throw new BuilderException("Undefined property `{$key}` expected string");
            }
        }
        $field->setTransformer($multiple ? $this->multiple : $this->single);
        if ($type === null) {
            throw new BuilderException('Invalid property type expected string');
        }
        $target = $map[$type] ?? null;
        if ($target instanceof MetadataBuilder) {
            $field->setOption('multiple', $multiple);
            $field->setOption('target', $target->getMetadata($map));
        }
    }
}
