<?php

namespace JsonApi\Normalizer;

use JsonApi\Metadata\MetadataInterface;
use JsonApi\Transformer\InvalidArgumentException;
use JsonApi\DataStorage\DataStorageInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @package JsonApi\Normalizer
 */
class Normalizer implements NormalizerInterface
{
    /**
     * @inheritDoc
     */
    public function normalize(MetadataInterface $metadata, $object, array $options = []): array
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

    /**
     * @param MetadataInterface $metadata
     * @param $object
     * @param array $options
     * @return array|null
     * @throws InvalidArgumentException
     */
    public function normalizeAttributes(MetadataInterface $metadata, $object, array $options): ?array
    {
        $data = [];
        foreach ($metadata->getAttributes() as $field) {
            $data[$field->getSerializeName()] = $field->getNormalizeValue($object);
        }
        return $data;
    }

    /**
     * @param MetadataInterface $metadata
     * @param $object
     * @param array $options
     * @return array|null
     * @throws InvalidArgumentException
     */
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
    public function denormalize(MetadataInterface $metadata, DataStorageInterface $storage, array $resource, array $options)
    {
        if ($metadata->isNew($resource['id'])) {
            $entity = $metadata->newInstanceWithoutConstructor();
            $arguments = [];
            foreach ($metadata->getConstructorArguments() as $field) {
                if ($field->isRelationship()) {
                    $arguments[] = $field->denormalize($resource['relationships'], $options);
                } else {
                    $arguments[] = $field->denormalize($resource['attributes'], $options);
                }
            }
            $metadata->invokeConstructor($entity, $arguments);
        } else {
            $entity = $storage->get($metadata, $resource['id']);
        }
        $this->denormalizeAttributes($metadata, $entity, $resource['attributes'], $options);
        $this->denormalizeRelationships($metadata, $entity, $resource['relationships'], $options);
        $this->denormalizeMeta($metadata, $entity, $resource['meta'], $options);
        return $entity;
    }

    public function denormalizeAttributes(MetadataInterface $metadata, $entity, array $attributes, array $options)
    {
        foreach ($metadata->getDenormalizedAttributes() as $field) {
            $field->setDenormalizeValue($entity, $attributes, $options);
        }
    }

    public function denormalizeRelationships(MetadataInterface $metadata, $entity, array $relationships, array $options)
    {
        foreach ($metadata->getDenormalizedRelationships() as $field) {
            $field->setDenormalizeValue($entity, $relationships, $options);
        }
    }

    public function denormalizeMeta(MetadataInterface $metadata, $entity, array $resource, array $options) {}
}
