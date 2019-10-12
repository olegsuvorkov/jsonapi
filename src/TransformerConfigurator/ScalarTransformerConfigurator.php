<?php

namespace JsonApi\TransformerConfigurator;

use JsonApi\Metadata\Field;
use JsonApi\MetadataBuilder\FieldBuilder;
use JsonApi\Transformer\TransformerInterface;

/**
 * @package JsonApi\TransformerConfigurator
 */
class ScalarTransformerConfigurator implements TransformerConfiguratorInterface
{
    /**
     * @var TransformerInterface
     */
    private $transformer;

    /**
     * @param TransformerInterface $transformer
     */
    public function __construct(TransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @inheritDoc
     */
    public function configure(Field $field, FieldBuilder $fieldBuilder, array $map): void
    {
        $field->setType($this->transformer->getType());
    }
}
