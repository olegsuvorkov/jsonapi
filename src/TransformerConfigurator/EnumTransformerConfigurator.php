<?php


namespace JsonApi\TransformerConfigurator;

use JsonApi\Exception\LoaderException;
use JsonApi\Metadata\Field;
use JsonApi\MetadataBuilder\FieldBuilder;
use JsonApi\Transformer\TransformerInterface;

class EnumTransformerConfigurator implements TransformerConfiguratorInterface
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
        $choices = $fieldBuilder->options['choices'] ?? $this;
        unset($fieldBuilder->options['choices']);
        if ($choices === $this) {
            throw new LoaderException();
        }
        if (is_array($choices) && $choices) {
            $field->setOption('choices', array_values($choices));
        } else {
            throw new LoaderException();
        }
    }

}