<?php

namespace JsonApi\Serializer\Normalizer;

use JsonApi\Metadata\Metadata;
use JsonApi\Serializer\Encoder\JsonVndApiEncoder;
use JsonApi\Transformer\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

/**
 * @package JsonApi\Serializer\Normalizer
 */
class DataNormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /**
     * @inheritDoc
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return $format === JsonVndApiEncoder::FORMAT &&
               is_array($data) &&
               array_key_exists('data', $data);
    }

    /**
     * @inheritDoc
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $included = $data['included'] ?? [];
        $data = $data['data'];
        if ($data === null) {
            return null;
        }
        if (!is_array($included)) {
            throw new InvalidArgumentException();
        }
        if (!is_array($data)) {
            throw new InvalidArgumentException();
        }
        $this->sortIncluded($included);
        foreach ($included as $item) {
            if (is_array($item)) {
                $this->denormalizer->denormalize($item, $item['type'] ?? null, $format, $context);
            } else {
                throw new InvalidArgumentException();
            }
        }
        if (isset($data['type']) && (isset($data['attributes']) || isset($data['relationships']))) {
            return $this->denormalizer->denormalize($data, $type, $format, $context);
        } else {
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->denormalizer->denormalize($item, $type, $format, $context);
            }
            return $result;
        }
    }

    private function sortIncluded(array &$list)
    {
        usort($list, function (array $left, array $right) {
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
        });
    }
}
