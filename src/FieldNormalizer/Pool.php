<?php

namespace JsonApi\FieldNormalizer;

/**
 * @package JsonApi\FieldNormalizer
 */
class Pool
{
    const RELATIONSHIP = 'relationship';
    const MULTIPLE_RELATIONSHIP = 'multiple_relationship';

    /**
     * @var FieldNormalizerInterface[]
     */
    private $types = [];

    /**
     * @param string $type
     * @return FieldNormalizerInterface
     * @throws UndefinedFieldTypeException
     */
    public function get(string $type)
    {
        if (isset($this->types[$type])) {
            return $this->types[$type];
        }
        throw UndefinedFieldTypeException::invalidType($type);
    }

    /**
     * @param FieldNormalizerInterface $fieldNormalizer
     */
    public function add(FieldNormalizerInterface $fieldNormalizer): void
    {
        $this->types[$fieldNormalizer->getType()] = $fieldNormalizer;
    }
}
