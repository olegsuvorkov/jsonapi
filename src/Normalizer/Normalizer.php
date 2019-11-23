<?php

namespace JsonApi\Normalizer;

use JsonApi\Metadata\MetadataInterface;

/**
 * @package JsonApi\Normalizer
 */
class Normalizer implements NormalizerInterface
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @inheritDoc
     */
    public function normalize(MetadataInterface $metadata, $object, array $options = [])
    {
        $data = [];
        if ($options['attributes'] && $metadata->getAttributes()) {
            $data['attributes'] = $this->normalizeAttributes($metadata, $object, $options);
        }
        $data['id'] = $metadata->getId($object);
        if ($meta = $this->normalizeMeta($metadata, $object, $options)) {
            $data['meta'] = $meta;
        }
        if ($options['relationships'] && $metadata->getRelationships()) {
            $data['relationships'] = $this->normalizeRelationships($metadata, $object, $options);
        }
        $data['type'] = $metadata->getType();
        return $data;
    }

    public function normalizeAttributes(MetadataInterface $metadata, $object, array $options): ?array
    {
        $data = [];
        foreach ($metadata->getAttributes() as $field) {
            $data[$field->getSerializeName()] = $field->getNormalizeValue($object);
        }
        return $data;
    }

    public function normalizeRelationships(MetadataInterface $metadata, $object, array $options): ?array
    {
        $data = [];
        foreach ($metadata->getRelationships() as $field) {
            $data[$field->getSerializeName()] = [
                'data' => $field->getNormalizeValue($object),
            ];
        }
        return $data;
    }

    public function normalizeMeta(MetadataInterface $metadata, $object, array $options): ?array
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function denormalize(MetadataInterface $metadata, $entity, array $resource, array $options)
    {
        if ($metadata->isNew($resource['id'])) {
            $arguments = [];
            foreach ($metadata->getConstructorArguments() as $field) {
                if ($field->isRelationship()) {
                    $arguments[] = $field->denormalize($resource['relationships'], $options);
                } else {
                    $arguments[] = $field->denormalize($resource['attributes'], $options);
                }
            }
            $metadata->invokeConstructor($entity, $arguments);
        }
        $this->denormalizeAttributes($metadata, $entity, $resource['attributes'], $options);
        $this->denormalizeRelationships($metadata, $entity, $resource['relationships'], $options);
        $this->denormalizeMeta($metadata, $entity, $resource['meta'], $options);
    }

    public function denormalizeAttributes(MetadataInterface $metadata, $entity, array $attributes, array $options)
    {
        foreach ($metadata->getDenormalizedAttributes() as $field) {
            $field->setDenormalizeValue($entity, $attributes, $options);
        }
    }

    public function denormalizeRelationships(MetadataInterface $metadata, $entity, array $relationships, array $options)
    {
        foreach ($metadata->getDenormalizedAttributes() as $field) {
            $field->setDenormalizeValue($entity, $relationships, $options);
        }
    }

    public function denormalizeMeta(MetadataInterface $metadata, $entity, array $resource, array $options) {}

    public function setSerializer(Serializer $serializer): void
    {
        $this->serializer = $serializer;
    }
}
