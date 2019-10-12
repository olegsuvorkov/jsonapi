<?php

namespace JsonApi\Serializer\Normalizer;

use JsonApi\Metadata\RegisterInterface;
use JsonApi\Metadata\UndefinedMetadataException;
use JsonApi\Serializer\Encoder\JsonVndApiEncoder;
use JsonApi\Transformer\TransformerPool;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

/**
 * @package JsonApi\Serializer\Normalizer
 */
class MetadataNormalizer implements ContextAwareNormalizerInterface
{
    /**
     * @var TransformerPool
     */
    private $pool;

    /**
     * @param TransformerPool $pool
     */
    public function __construct(TransformerPool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * @inheritDoc
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var RegisterInterface $register */
        $register = $context['register'];
        try {
            $metadata = $register->getByClass($object);
        } catch (UndefinedMetadataException $e) {
            throw new LogicException($e->getMessage(), $e->getCode(), $e);
        }
        $attributes = [];
        foreach ($metadata->getAttributes() as $field) {
            $attributes[$field->getSerializeName()] = $field->getNormalizeValue($object, $this->pool);
        }
        $relationships = [];
        foreach ($metadata->getRelationships() as $field) {
            $relationships[$field->getSerializeName()] = [
                'data' => $field->getNormalizeValue($object, $this->pool),
            ];
        }

        return [
            'attributes'    => $attributes,
            'id'            => $metadata->getId($object, $this->pool),
            'relationships' => $relationships,
            'type'          => $metadata->getType(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, $format = null, array $context = [])
    {
        try {
            return $format === JsonVndApiEncoder::FORMAT &&
                   is_object($data) &&
                   !is_iterable($data) &&
                   isset($context['register']) &&
                   $context['register'] instanceof RegisterInterface &&
                   $context['register']->getByClass($data);
        } catch (UndefinedMetadataException $e) {
            return false;
        }
    }
}
