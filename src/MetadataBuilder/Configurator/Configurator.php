<?php

namespace JsonApi\MetadataBuilder\Configurator;

use JsonApi\Metadata\Field;
use JsonApi\Transformer\TransformerInterface;

/**
 * @package JsonApi\MetadataBuilder\Configurator
 */
class Configurator implements ConfiguratorInterface
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
    public function configure(Field $field, array $options, array $map): void
    {
        $field->setTransformer($this->transformer);
    }
}
