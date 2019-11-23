<?php

namespace JsonApi\Normalizer;

use JsonApi\Context\ContextInterface;
use JsonApi\DataStorage\CreationDataStorage;
use JsonApi\DataStorage\DataStorageInterface;
use JsonApi\Metadata\UndefinedMetadataException;
use JsonApi\Transformer\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;

/**
 * @package JsonApi\Normalizer
 */
class Serializer implements SerializerInterface
{
    /**
     * @var NormalizerInterface[]
     */
    private $normalizers = [];

    /**
     * @var NormalizerInterface
     */
    private $defaultNormalizer;

    /**
     * @var DataStorageInterface
     */
    private $storage;

    /**
     * @var array
     */
    private static $defaultResource = [
        'type' => '',
        'attributes' => [],
        'relationships' => [],
        'id' => null,
        'meta' => [],
    ];
    /**
     * @var JsonEncode
     */
    private $encoder;
    /**
     * @var JsonDecode
     */
    private $decode;

    /**
     * @param NormalizerInterface[] $normalizers
     * @param NormalizerInterface $defaultNormalizer
     * @param DataStorageInterface $storage
     */
    public function __construct(
        array $normalizers,
        NormalizerInterface $defaultNormalizer,
        DataStorageInterface $storage
    ) {
        $this->storage = $storage;
        $this->defaultNormalizer = $defaultNormalizer;
        $this->normalizers = [];
        foreach ($normalizers as $type => $normalizer) {
            if ($normalizer instanceof TypeNormalizerInterface) {
                $type = $normalizer->getType();
            }
            if ($normalizer instanceof SerializerAwareInterface) {
                $normalizer->setSerializer($this);
            }
            $this->normalizers[$type] = $normalizer;
        }
        $options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
        $options|= JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        $this->encoder = new JsonEncode(['json_encode_options' => $options]);
        $this->decode = new JsonDecode(['json_decode_associative' => true]);
    }

    public function serialize($data, array $options, ContextInterface $context): string
    {
        $data = $this->normalize($data, $options, $context);
        return $this->encoder->encode($data, 'json');
    }


    public function deserialize(Request $request, array $options, ContextInterface $context): array
    {
        $data = $this->decode->decode($request->getContent(), 'json');
        return $this->denormalize($data, $options, $context);
    }

    /**
     * @inheritDoc
     * @throws UndefinedMetadataException
     */
    public function normalize($data, array $options, ContextInterface $context): array
    {
        $options['meta'] = $context->getMeta();
        $structure   = [];
        $included    = [];
        if (is_iterable($data)) {
            $list = is_array($data) ? $data : iterator_to_array($data);
            $primaryData = [];
            foreach ($list as $item) {
                $primaryData[] = $this->normalizePrimaryResource($item, $options, $context, $included);
            }
            $structure['data'] = $primaryData;
        } elseif (is_object($data)) {
            $list = [$data];
            $structure['data'] = $this->normalizePrimaryResource($data, $options, $context, $included);
        } else {
            throw new UndefinedMetadataException();
        }
        if ($included) {
            $structure['included'] = [];
            foreach ($included as $item) {
                if (!in_array($item, $list, true)) {
                    $structure['included'][] = $this->normalizeIncludedResource($item, $options, $context);
                }
            }
        }
        return $structure;
    }

    /**
     * @param object $item
     * @param array $options
     * @param ContextInterface $context
     * @param array $included
     * @return array
     * @throws UndefinedMetadataException
     */
    private function normalizePrimaryResource($item, array $options, ContextInterface $context, array &$included): array
    {
        $metadata = $context->getByClass($item);
        $context->getInclude()->register($metadata, $item, $included);
        $normalizer = $this->normalizers[$metadata->getType()] ?? $this->defaultNormalizer;
        return $normalizer->normalize($metadata, $item, $options);
    }

    /**
     * @param object           $item
     * @param array            $options
     * @param ContextInterface $context
     * @return array
     * @throws UndefinedMetadataException
     */
    private function normalizeIncludedResource($item, array $options, ContextInterface $context): array
    {
        $metadata = $context->getByClass($item);
        $normalizer = $this->normalizers[$metadata->getType()] ?? $this->defaultNormalizer;
        return $normalizer->normalize($metadata, $item, $options);
    }

    /**
     * @inheritDoc
     */
    public function denormalize(array $structure, array $options, ContextInterface $context)
    {
        $storage = empty($options['allow_create']) ? $this->storage : new CreationDataStorage($this->storage);
        $structure = array_merge(['data' => null, 'included' => null], $structure);
        foreach ($structure as $key => $value) {
            if ($key === 'data' || $key === 'included') {
                if (!is_array($value)) {
                    throw new InvalidArgumentException();
                }
            } else {
                throw new InvalidArgumentException();
            }
        }
        if (is_array($structure['data'])) {
            if (isset($structure['included'])) {
                $included = $structure['included'];
                if (is_array($included)) {
                    usort($included, [$this, 'sortCallback']);
                    $this->denormalizeResources($structure['data'], $context, $storage, $options);
                } else {
                    throw new InvalidArgumentException('Invalid included resource');
                }
            }
            if (isset($structure['data']['type'])) {
                return $this->denormalizeResource($structure['data'], $context, $storage, $options);
            } else {
                return $this->denormalizeResources($structure['data'], $context, $storage, $options);
            }
        }
        throw new InvalidArgumentException();
    }

    /**
     * @param $resources
     * @param ContextInterface $context
     * @param DataStorageInterface $storage
     * @param array $options
     * @return array
     * @throws InvalidArgumentException
     * @throws UndefinedMetadataException
     */
    private function denormalizeResources($resources, ContextInterface $context, DataStorageInterface $storage, array $options)
    {
        $data = [];
        foreach ($resources as $resource) {
            $data[] = $this->denormalizeResource($resource, $context, $storage, $options);
        }
        return $data;
    }

    /**
     * @param $resource
     * @param ContextInterface $context
     * @param DataStorageInterface $storage
     * @param array $options
     * @return mixed
     * @throws InvalidArgumentException
     * @throws UndefinedMetadataException
     */
    private function denormalizeResource($resource, ContextInterface $context, DataStorageInterface $storage, array $options)
    {
        if (is_array($resource)) {
            $resource = $this->validateResource($resource);
            $metadata = $context->getByType($resource['type']);
            $normalizer = $this->normalizers[$metadata->getType()] ?? $this->defaultNormalizer;
            return $normalizer->denormalize($metadata, $storage, $resource, $options);
        }
        throw new InvalidArgumentException();
    }

    /**
     * @param array $resource
     * @return array
     * @throws InvalidArgumentException
     */
    private function validateResource(array $resource)
    {
        $resource = array_merge(self::$defaultResource, $resource);
        foreach ($resource as $key => $value) {
            if ($key === 'attributes' || $key === 'relationships' || $key === 'meta') {
                if (!is_array($value)) {
                    throw new InvalidArgumentException();
                }
            } elseif ($key === 'id' || $key === 'type') {
                if (!is_string($value)) {
                    throw new InvalidArgumentException();
                }
            } else {
                throw new InvalidArgumentException();
            }
        }
        return $resource;
    }

    /**
     * @param array $left
     * @param array $right
     * @return int
     */
    public function sortCallback(array $left, array $right)
    {
        if ($left['id'] === $right['id'] && $left['type'] === $right['type']) {
            return 0;
        } else {
            $relationships = $right['relationships'] ?? [];
            foreach ($relationships as $relationship) {
                $data = $relationship['data'];
                if ($left['id'] === $data['id'] && $left['type'] === $data['type']) {
                    return -1;
                }
            }
            return 1;
        }
    }
}
