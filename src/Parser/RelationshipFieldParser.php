<?php

namespace JsonApi\Parser;

use JsonApi\Exception\LoaderException;
use JsonApi\FieldNormalizer\FieldNormalizerInterface;
use JsonApi\FieldNormalizer\Pool;
use JsonApi\Metadata\Field;
use JsonApi\Metadata\RegisterInterface;
use JsonApi\Metadata\UndefinedMetadataException;

/**
 * @package JsonApi\Parser
 */
class RelationshipFieldParser extends AbstractFieldParser
{
    /**
     * @var RegisterInterface
     */
    private $register;

    /**
     * @param RegisterInterface $register
     */
    public function __construct(RegisterInterface $register)
    {
        $this->register = $register;
    }

    /**
     * @inheritDoc
     * @throws UndefinedMetadataException
     */
    public function parseField(Field $field, &$parameters): void
    {
        $multiple = $parameters['multiple'] ?? false;
        unset($parameters['multiple']);
        if ($multiple === true) {
            $field->setType(Pool::MULTIPLE_RELATIONSHIP);
        } elseif ($multiple === false) {
            $field->setType(Pool::RELATIONSHIP);
        } else {
            throw new LoaderException();
        }
        $type = $parameters['type'] ?? $this;
        unset($parameters['type']);
        if ($type === $this) {
            throw new LoaderException();
        }
        if (is_string($type)) {
            $this->register->getByType($type);
            $field->setOption('type', $type);
        } else {
            throw new LoaderException();
        }
        parent::parseField($field, $parameters);
        if ($parameters) {
            throw new LoaderException();
        }
    }
}
