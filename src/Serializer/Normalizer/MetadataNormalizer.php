<?php

namespace JsonApi\Serializer\Normalizer;

use JsonApi\DataStorage\DataStorageInterface;
use JsonApi\Metadata\MetadataInterface;
use JsonApi\Metadata\RegisterInterface;
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
     * @var array
     */
    private static $defaults = [
        'id'            => null,
        'type'          => null,
        'attributes'    => [],
        'relationships' => [],
    ];

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
        if (empty($context['only_identity']) && $metadata->getAttributes()) {
            $attributes = [];
            foreach ($metadata->getAttributes() as $field) {
                $attributes[$field->getSerializeName()] = $field->getNormalizeValue($object);
            }
            $data['attributes'] = $attributes;
        }
        $data['id'] = $metadata->getId($object);
        if (empty($context['only_identity']) && $metadata->getRelationships()) {
            $relationships = [];
            foreach ($metadata->getRelationships() as $field) {
                $relationships[$field->getSerializeName()] = [
                    'data' => $field->getNormalizeValue($object),
                ];
            }
            $data['relationships'] = $relationships;
        }
        $data['type'] = $metadata->getType();
        return $data;
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
        $storage = $context['storage'] ?? $this->storage;
        
        if (is_array($data)) {
            $data = array_merge(self::$defaults, $data);
        } else {
            throw new InvalidArgumentException();
        }
        $id = $data['id'];
        if ($id !== null && !is_string($id)) {
            throw new InvalidArgumentException();
        }
        $attributes = $data['attributes'];
        if (!is_array($attributes)) {
            throw new InvalidArgumentException();
        }
        $relationships = $data['relationships'];
        if (!is_array($relationships)) {
            throw new InvalidArgumentException();
        }
        if (is_string($data['type'])) {
            $metadata = $this->register->getByType($type)->getMetadataByType($data['type']);
        } else {
            throw new InvalidArgumentException();
        }
        $entity = $storage->get($metadata, $id);
        if ($storage->isNew($entity)) {
            $arguments = [];
            foreach ($metadata->getConstructorArguments() as $field) {
                if ($field->isRelationship()) {
                    $arguments[] = $field->denormalize($relationships, $context);
                } else {
                    $arguments[] = $field->denormalize($attributes, $context);
                }
            }
            $metadata->invokeConstructor($entity, $arguments);
        }
        foreach ($metadata->getDenormalizedAttributes() as $field) {
            $field->setDenormalizeValue($entity, $attributes, $context);
        }
        foreach ($metadata->getDenormalizedRelationships() as $field) {
            $field->setDenormalizeValue($entity, $relationships, $context);
        }
        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return $format === JsonVndApiEncoder::FORMAT && $this->register->hasType($type);
    }
}
