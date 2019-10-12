<?php

namespace JsonApi\MetadataBuilder;

use JsonApi\Exception\LoaderException;

/**
 * @package JsonApi\MetadataBuilder
 */
interface FieldBuilderFactoryInterface
{
    /**
     * @param MetadataBuilder $builder
     * @param $fields
     * @return FieldBuilder[]
     * @throws LoaderException
     */
    public function createFieldBuilderList(MetadataBuilder $builder, $fields): array;

    /**
     * @param MetadataBuilder $metadataBuilder
     * @param $name
     * @param $parameters
     * @return FieldBuilder
     * @throws LoaderException
     */
    public function createFieldBuilder(MetadataBuilder $metadataBuilder, string $name, $parameters): FieldBuilder;
}
