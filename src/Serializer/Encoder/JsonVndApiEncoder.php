<?php

namespace JsonApi\Serializer\Encoder;

use JsonApi\Context\ContextInterface;
use JsonApi\ContextInclude\IncludeStack;
use JsonApi\ContextInclude\IncludeStackInterface;
use JsonApi\Exception\ParseUrlException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\NormalizationAwareInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class JsonVndApiEncoder implements EncoderInterface, NormalizationAwareInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    const FORMAT = 'application/vnd.api+json';

    /**
     * @var JsonEncoder
     */
    private $encoder;

    /**
     * JsonVndApiEncoder constructor.
     */
    public function __construct()
    {
        $this->encoder = new JsonEncode();
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
        if (is_iterable($data)) {
            if (!is_array($data)) {
                $data = iterator_to_array($data);
            }
            $stack = new IncludeStack($data);
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->normalize($item, $fetchContext, $context, $stack);
            }
        } else {
            $stack = new IncludeStack([$data]);
            $result = $this->normalize($data, $fetchContext, $context, $stack);
        }
        $included = [];
        foreach ($stack->all() as $item) {
            $included[] = $this->normalize($item, $fetchContext, $context);
        }
        $data = [
            'data'     => $result,
            'included' => $included,
        ];
        $options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
        $options|= JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        return $this->encoder->encode($data, $format, array_merge($context, [
            'json_encode_options' => $options,
        ]));
    }

    /**
     * @inheritDoc
     */
    public function supportsEncoding($format)
    {
        return $format === self::FORMAT;
    }

    private function normalize($data, ContextInterface $fetchContext, array $context, IncludeStackInterface $stack = null)
    {
        $metadata = $fetchContext->getByClass($data);
        $context['metadata'] = $metadata;
        if ($stack) {
            $fetchContext->getInclude()->register($metadata, $data, $stack);
        }
        return $this->serializer->normalize($data, self::FORMAT, $context);
    }

    /**
     * @inheritDoc
     */
    public static function checkAcceptable(?Request $request): bool
    {
        return $request && in_array(self::FORMAT, $request->getAcceptableContentTypes());
    }
}
