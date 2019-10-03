<?php


namespace JsonApi\Parser;


use JsonApi\Exception\LoaderException;
use JsonApi\FieldNormalizer\ConfigureFieldNormalizerInterface;
use JsonApi\FieldNormalizer\Pool;
use JsonApi\Metadata\Field;

class AttributeFieldParser extends AbstractFieldParser
{
    /**
     * @var Pool
     */
    private $pool;

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * @inheritDoc
     */
    public function parseField(Field $field, &$parameters): void
    {
        parent::parseField($field, $parameters);
        $type = $parameters['type'] ?? $this;
        unset($parameters['type']);
        if ($type === $this) {
            throw new LoaderException();
        }
        if (!is_string($type)) {
            throw new LoaderException();
        }
        $fieldNormalizer = $this->pool->get($type);
        if ($fieldNormalizer instanceof ConfigureFieldNormalizerInterface) {
            $fieldNormalizer->configureAttribute($field, $parameters);
        }
        if ($parameters) {
            throw new LoaderException(sprintf('Undefined parameters "%s"', implode('", "', array_keys($parameters))));
        }
    }
}
