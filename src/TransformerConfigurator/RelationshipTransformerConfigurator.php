<?php

namespace JsonApi\TransformerConfigurator;

use JsonApi\Exception\LoaderException;
use JsonApi\Metadata\Field;
use JsonApi\MetadataBuilder\FieldBuilder;
use JsonApi\MetadataBuilder\MetadataBuilder;
use JsonApi\Transformer\TransformerInterface;

/**
 * @package JsonApi\TransformerConfigurator
 */
class RelationshipTransformerConfigurator implements TransformerConfiguratorInterface
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
    public function configure(Field $field, FieldBuilder $fieldBuilder, array $map): void
    {
        $multiple = $fieldBuilder->options['multiple'] ?? false;
        if (!is_bool($multiple)) {
            throw new LoaderException('Invalid property multiple expected bool');
        }
        $field->setType($multiple ? $this->multiple->getType() : $this->single->getType());
        $type = $fieldBuilder->options['type'] ?? null;
        if (!is_string($type)) {
            throw new LoaderException('Invalid property type expected string');
        }
        $target = $map[$type] ?? null;
        if ($target instanceof MetadataBuilder) {
            $field->setOption('target', $target->getMetadata($map));
        }
    }
}
