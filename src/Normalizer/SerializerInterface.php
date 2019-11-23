<?php

namespace JsonApi\Normalizer;

use JsonApi\Context\ContextInterface;
use JsonApi\Metadata\UndefinedMetadataException;
use JsonApi\Transformer\InvalidArgumentException;

/**
 * @package JsonApi\Normalizer
 */
interface SerializerInterface
{
    const FORMAT = 'application/vnd.api+json';

    /**
     * @param $data
     * @param $options
     * @param ContextInterface $context
     * @return string
     * @throws UndefinedMetadataException
     */
    public function serialize($data, array $options, ContextInterface $context): string;

    /**
     * @param array $structure
     * @param array $options
     * @param ContextInterface $context
     * @return object|object[]
     * @throws InvalidArgumentException
     * @throws UndefinedMetadataException
     */
    public function denormalize(array $structure, array $options, ContextInterface $context);
}
