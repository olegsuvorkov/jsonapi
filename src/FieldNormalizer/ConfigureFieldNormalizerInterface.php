<?php

namespace JsonApi\FieldNormalizer;

use JsonApi\Exception\LoaderException;
use JsonApi\Metadata\FieldInterface;

/**
 * @package JsonApi\FieldNormalizer
 */
interface ConfigureFieldNormalizerInterface extends FieldNormalizerInterface
{
    /**
     * @param FieldInterface $field
     * @param array $parameters
     * @throws LoaderException
     */
    public function configureAttribute(FieldInterface $field, array &$parameters): void;
}
