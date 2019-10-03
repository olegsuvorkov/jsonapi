<?php

namespace JsonApi\Serializer\Encoder;

use Symfony\Component\HttpFoundation\RequestStack;
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
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var JsonEncoder
     */
    private $encoder;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->encoder = new JsonEncode();
    }

    /**
     * @inheritDoc
     */
    public function encode($data, $format, array $context = [])
    {
        $data = $this->serializer->normalize($data, self::FORMAT, $context);
        $data = [
            'data' => $data,
        ];
        return $this->encoder->encode($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsEncoding($format)
    {
        return $format === self::FORMAT ||
               (
                    $this->encoder->supportsEncoding($format) &&
                    $this->checkAcceptable()
               );
    }

    private function checkAcceptable()
    {
        return in_array(self::FORMAT, $this->requestStack->getMasterRequest()->getAcceptableContentTypes());
    }
}
