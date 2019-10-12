<?php

namespace JsonApi\TransformerConfigurator;

use JsonApi\Exception\LoaderException;
use JsonApi\Metadata\Field;
use JsonApi\MetadataBuilder\FieldBuilder;

/**
 * @package JsonApi\TransformerConfigurator
 */
interface TransformerConfiguratorInterface
{
    /**
     * @param Field $field
     * @param FieldBuilder $fieldBuilder
     * @param array $map
     * @return void
     * @throws LoaderException
     */
    public function configure(Field $field, FieldBuilder $fieldBuilder, array $map): void;
}
