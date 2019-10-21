<?php

namespace JsonApi\Serializer\Encoder;

use JsonApi\Context\ContextInterface;
use JsonApi\Exception\ParseUrlException;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * JsonVndApiEncoder constructor.
     */
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
     * @throws ParseUrlException
     */
    public function encode($data, $format, array $context = [])
    {
        $fetchContext = $context['context'] ?? null;
        if (!$fetchContext) {
            throw new ParseUrlException();
        }
        $data = $this->getData($data);
        $list = is_array($data) ? $data : [$data];
        $stack = [];
        if (is_array($data)) {
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->normalize($item, $fetchContext, $context, $stack);
            }
        } else {
            $result = $this->normalize($data, $fetchContext, $context, $stack);
        }
        $included = [];
        foreach ($stack as $item) {
            if (!in_array($item, $list, true)) {
                $included[] = $this->normalize($item, $fetchContext, $context);
            }
        }
        return $this->encoder->encode([
            'data'     => $result,
            'included' => $included,
        ], $format, $context);
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

    /**
     * @inheritDoc
     */
    public static function checkContentType(?Request $request): bool
    {
        return $request && self::FORMAT === $request->headers->get('Content-Type');
    }

    private function normalize($data, ContextInterface $fetchContext, array $context, array &$stack = null)
    {
        $metadata = $fetchContext->getByClass($data);
        $context['metadata'] = $metadata;
        if ($stack !== null) {
            $fetchContext->getInclude()->register($metadata, $data, $stack);
        }
        return $this->serializer->normalize($data, self::FORMAT, $context);
    }

    private function getData($data)
    {
        if (is_iterable($data)) {
            if (!is_array($data)) {
                $data = iterator_to_array($data);
            }
        }
        return $data;
    }

    /**
     * @inheritDoc
     */
    public static function checkAcceptable(?Request $request): bool
    {
        return $request && in_array(self::FORMAT, $request->getAcceptableContentTypes());
    }
}
