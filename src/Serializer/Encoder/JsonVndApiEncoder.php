<?php

namespace JsonApi\Serializer\Encoder;

use JsonApi\Context\ContextInterface;
use JsonApi\ContextInclude\ContextInclude;
use JsonApi\Exception\ParseUrlException;
use JsonApi\Metadata\UndefinedMetadataException;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\NormalizationAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class JsonVndApiEncoder implements EncoderInterface,
    NormalizationAwareInterface,
    DecoderInterface,
    DenormalizerAwareInterface,
    SerializerAwareInterface
{
    use SerializerAwareTrait;
    use DenormalizerAwareTrait;

    const FORMAT = 'application/vnd.api+json';

    /**
     * @var JsonEncoder
     */
    private $encoder;

    /**
     * @var JsonDecode
     */
    private $decode;

    public function __construct()
    {
        $options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
        $options|= JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        $this->encoder = new JsonEncode([
            'json_encode_options' => $options,
        ]);
        $this->decode = new JsonDecode([
            'json_decode_associative' => true,
        ]);
    }

    /**
     * @inheritDoc
     * @throws UndefinedMetadataException
     */
    public function encode($list, $format, array $context = [])
    {
        if (isset($context['context'])) {
            /** @var ContextInterface $registerContext */
            $registerContext = $context['context'];
            if (is_iterable($list) && !is_array($list)) {
                $list = iterator_to_array($list);
            }
            $relationship = $context['relationship'] ?? null;
            unset($context['relationship']);
            $isMultiple = is_array($list);
            $list = $isMultiple ? $list : [$list];
            $include = $registerContext->getInclude();
            if ($relationship) {
                $include = $include->findBy($relationship) ?? new ContextInclude();
                $stack = $list;
            } else {
                $stack = [];
            }
            $data = ['data' => []];
            foreach ($list as $item) {
                $context['metadata'] = $registerContext->getByClass($item);
                $include->register($context['metadata'], $item, $stack);
                $data['data'][] = $this->serializer->normalize($item, self::FORMAT, $context);
            }
            if ($relationship) {
                $list = [];
            }
            if ($stack) {
                unset($context['only_identity']);
                $data['included'] = [];
                foreach ($stack as $item) {
                    if (!in_array($item, $list, true)) {
                        $context['metadata'] = $registerContext->getByClass($item);
                        $data['included'][] = $this->serializer->normalize($item, self::FORMAT, $context);
                    }
                }
            }
            if (!$isMultiple) {
                $data['data'] = array_shift($data['data']);
            }
        } else {
            $data = $list;
        }
        return $this->encoder->encode($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsEncoding($format)
    {
        return $format === self::FORMAT;
    }

    /**
     * @inheritDoc
     */
    public function decode($data, $format, array $context = [])
    {
        return $this->decode->decode($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsDecoding($format)
    {
        return $format === self::FORMAT;
    }
}
