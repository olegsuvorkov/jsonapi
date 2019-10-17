<?php

namespace JsonApi\MetadataBuilder\Configurator;

use JsonApi\Metadata\Field;
use JsonApi\MetadataBuilder\BuilderException;

/**
 * @package JsonApi\MetadataBuilder\Configurator
 */
interface ConfiguratorInterface
{
    /**
     * @param Field $field
     * @param array $options
     * @param array $map
     * @return void
     * @throws BuilderException
     */
    public function configure(Field $field, array $options, array $map): void;
}
