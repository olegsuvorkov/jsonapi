<?php

namespace JsonApi\Serializer\Normalizer;

use JsonApi\DataStorage\DataStorageInterface;
use JsonApi\Metadata\MetadataInterface;
use JsonApi\Metadata\RegisterInterface;
use JsonApi\Metadata\UndefinedMetadataException;
use JsonApi\Serializer\Encoder\JsonVndApiEncoder;
use JsonApi\Transformer\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

/**
 * @package JsonApi\Serializer\Normalizer
 */
class MetadataNormalizer implements ContextAwareNormalizerInterface,
                                    ContextAwareDenormalizerInterface,
                                    CacheableSupportsMethodInterface
{
    /**
     * @var RegisterInterface
     */
    private $register;
    /**
     * @var DataStorageInterface
     */
    private $storage;

    /**
     * @param DataStorageInterface $storage
     * @param RegisterInterface $register
     */
    public function __construct(DataStorageInterface $storage, RegisterInterface $register)
    {
        $this->register = $register;
        $this->storage = $storage;
    }

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

    /**
     * @inheritDoc
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException();
        }
        [$id, $attributes, $relationships] = $this->getDenormalizedData($type, $data);
        $metadata = $this->register->getByType($type);
        $entity = $this->storage->get($metadata, $id);
        if ($this->storage->isNew($entity)) {
            $arguments = [];
            foreach ($metadata->getConstructorArguments() as $field) {
                if ($metadata->containsRelationship($field)) {
                    $arguments[] = $field->denormalize($relationships);
                } else {
                    $arguments[] = $field->denormalize($attributes);
                }
            }
            $metadata->invokeConstructor($entity, $arguments);
        }
        foreach ($metadata->getAttributes() as $field) {
            if (!$metadata->isConstructorArgument($field)) {
                $field->setDenormalizeValue($entity, $attributes);
            }
        }
        foreach ($metadata->getRelationships() as $field) {
            if (!$metadata->isConstructorArgument($field)) {
                $field->setDenormalizeValue($entity, $relationships);
            }
        }
        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        try {
            return $format === JsonVndApiEncoder::FORMAT && $this->register->getByType($type);
        } catch (UndefinedMetadataException $e) {
            return false;
        }
    }

    private function getDenormalizedData(string $type, array $data): array
    {

        $id = null;
        $attributes = [];
        $relationships = [];
        foreach ($data as $key => $value) {
            if ($key === 'id') {
                if (is_string($value)) {
                    $id = $value;
                } else {
                    throw new InvalidArgumentException();
                }
            } elseif ($key === 'type') {
                if ($value !== $type) {
                    throw new InvalidArgumentException();
                }
            } elseif ($key === 'attributes') {
                if (is_array($value)) {
                    $attributes = $value;
                } else {
                    throw new InvalidArgumentException();
                }
            } elseif ($key === 'relationships') {
                if (is_array($value)) {
                    $relationships = $value;
                } else {
                    throw new InvalidArgumentException();
                }
            } else {
                throw new InvalidArgumentException();
            }
        }
        return [$id, $attributes, $relationships];
    }
}
