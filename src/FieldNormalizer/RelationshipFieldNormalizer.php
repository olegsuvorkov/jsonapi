<?php

namespace JsonApi\FieldNormalizer;

use JsonApi\Metadata\FieldInterface;
use JsonApi\Metadata\RegisterInterface;

/**
 * @package JsonApi\FieldNormalizer
 */
class RelationshipFieldNormalizer implements FieldNormalizerInterface
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
     */
    public function getType(): string
    {
        return Pool::RELATIONSHIP;
    }

    /**
     * @inheritDoc
     */
    public function normalize(FieldInterface $field, $data)
    {
        // TODO: Implement normalize() method.
    }

    /**
     * @inheritDoc
     */
    public function denormalize(FieldInterface $field, $data)
    {
        // TODO: Implement denormalize() method.
    }
}
