<?php

namespace JsonApi\Serializer\Normalizer;

use JsonApi\Metadata\MetadataInterface;
use JsonApi\Serializer\Encoder\JsonVndApiEncoder;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

/**
 * @package JsonApi\Serializer\Normalizer
 */
class MetadataNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * @inheritDoc
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var MetadataInterface $metadata */
        $metadata = $context['metadata'];
        $attributes = [];
        foreach ($metadata->getAttributes() as $field) {
            $attributes[$field->getSerializeName()] = $field->getNormalizeValue($object);
        }
        $relationships = [];
        foreach ($metadata->getRelationships() as $field) {
            $relationships[$field->getSerializeName()] = [
                'data' => $field->getNormalizeValue($object),
            ];
        }

        return [
            'attributes'    => $attributes,
            'id'            => $metadata->getId($object),
            'relationships' => $relationships,
            'type'          => $metadata->getType(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return $format === JsonVndApiEncoder::FORMAT &&
               is_object($data) &&
               !is_iterable($data) &&
               isset($context['metadata']) &&
               $context['metadata'] instanceof MetadataInterface;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return __CLASS__ === \get_class($this);
    }
}
