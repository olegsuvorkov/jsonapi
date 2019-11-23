<?php

namespace JsonApi\Normalizer;

use JsonApi\Metadata\MetadataInterface;

/**
 * @package JsonApi\Normalizer
 */
interface NormalizerInterface
{
    public function normalize(MetadataInterface $metadata, $object, array $options = []);

    public function denormalize(MetadataInterface $metadata, $entity, array $resource, array $context);

    public function setSerializer(Serializer $serializer): void;
}
