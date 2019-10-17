<?php

namespace JsonApi\MetadataBuilder\Configurator;

use JsonApi\Metadata\Field;
use JsonApi\MetadataBuilder\BuilderException;

/**
 * @package JsonApi\MetadataBuilder\Configurator
 */
class AttributeConfigurator implements ConfiguratorInterface
{
    /**
     * @var ConfiguratorInterface[]
     */
    private $types;

    /**
     * @param ConfiguratorInterface[] $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /**
     * @inheritDoc
     */
    public function configure(Field $field, array $options, array $map): void
    {
        if (array_key_exists('type', $options)) {
            if (is_string($options['type'])) {
                $type = $options['type'];
                if (isset($this->types[$type])) {
                    $this->types[$type]->configure($field, $options, $map);
                } else {
                    throw new BuilderException(sprintf('Invalid attribute `type` value `%s`', $type));
                }
            } else {
                throw new BuilderException('Expected string attribute `type`');
            }
        } else {
            throw new BuilderException('Required attribute `type`');
        }
    }
}
