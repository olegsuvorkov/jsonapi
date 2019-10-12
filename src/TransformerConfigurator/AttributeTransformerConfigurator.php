<?php

namespace JsonApi\TransformerConfigurator;

use JsonApi\Exception\LoaderException;
use JsonApi\Metadata\Field;
use JsonApi\MetadataBuilder\FieldBuilder;

/**
 * @package JsonApi\TransformerConfigurator
 */
class AttributeTransformerConfigurator implements TransformerConfiguratorInterface
{
    /**
     * @var TransformerConfiguratorInterface[]
     */
    private $types;

    /**
     * @param TransformerConfiguratorInterface[] $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /**
     * @inheritDoc
     */
    public function configure(Field $field, FieldBuilder $fieldBuilder, array $map): void
    {
        if (array_key_exists('type', $fieldBuilder->options)) {
            if (is_string($fieldBuilder->options['type'])) {
                $type = $fieldBuilder->options['type'];
                if (isset($this->types[$type])) {
                    $this->types[$type]->configure($field, $fieldBuilder, $map);
                } else {
                    throw new LoaderException(sprintf('Invalid attribute `type` value `%s`', $type));
                }
            } else {
                throw new LoaderException('Expected string attribute `type`');
            }
        } else {
            throw new LoaderException('Required attribute `type`');
        }
    }
}
