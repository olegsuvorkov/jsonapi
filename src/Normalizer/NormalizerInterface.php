<?php

namespace JsonApi\Normalizer;

use JsonApi\Metadata\MetadataInterface;
use JsonApi\Transformer\InvalidArgumentException;

/**
 * @package JsonApi\Normalizer
 */
interface NormalizerInterface
{
    /**
     * @param MetadataInterface $metadata
     * @param $object
     * @param array $options
     * @return array
     * @throws InvalidArgumentException
     */
    public function normalize(MetadataInterface $metadata, $object, array $options = []): array;

    /**
     * @param MetadataInterface $metadata
     * @param $entity
     * @param array $resource
     * @param array $context
     * @return mixed
     */
    public function denormalize(MetadataInterface $metadata, $entity, array $resource, array $context);
}
