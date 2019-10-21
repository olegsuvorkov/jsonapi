<?php

namespace JsonApi\MetadataBuilder;

/**
 * @package JsonApi\MetadataBuilder
 */
interface FieldBuilderFactoryInterface
{
    /**
     * @param MetadataBuilder $builder
     * @param $fields
     * @return FieldBuilder[]
     * @throws BuilderException
     */
    public function createFieldBuilderList(MetadataBuilder $builder, $fields): array;


    /**
     * @param MetadataBuilder $builder
     * @param $fields
     * @return FieldBuilder[]
     * @throws BuilderException
     */
    public function createIdentifierBuilderList(MetadataBuilder $builder, $fields): array;

    /**
     * @param MetadataBuilder $builder
     * @param $fields
     * @return FieldBuilder[]
     * @throws BuilderException
     */
    public function createConstructorArgumentsBuilderList(MetadataBuilder $builder, $fields): array;
}
