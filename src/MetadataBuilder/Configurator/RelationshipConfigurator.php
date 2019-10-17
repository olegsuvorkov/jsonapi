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
        $multiple = $options['multiple'] ?? false;
        if (!is_bool($multiple)) {
            throw new BuilderException('Invalid property multiple expected bool');
        }
        $field->setTransformer($multiple ? $this->multiple : $this->single);
        $type = $options['type'] ?? null;
        if (!is_string($type)) {
            throw new BuilderException('Invalid property type expected string');
        }
        $target = $map[$type] ?? null;
        if ($target instanceof MetadataBuilder) {
            $field->setOption('multiple', $multiple);
            $field->setOption('target', $target->getMetadata($map));
        }
    }
}
