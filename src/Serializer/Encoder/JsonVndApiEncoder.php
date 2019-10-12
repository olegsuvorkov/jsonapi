<?php

namespace JsonApi\Serializer\Encoder;

use JsonApi\Metadata\ContextRegisterFactory;
use JsonApi\Metadata\RegisterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
    /**
     * @var ContextRegisterFactory
     */
    private $contextRegisterFactory;

    /**
     * @param RequestStack $requestStack
     * @param ContextRegisterFactory $contextRegisterFactory
     */
    public function __construct(RequestStack $requestStack, ContextRegisterFactory $contextRegisterFactory)
    {
        $this->requestStack = $requestStack;
        $this->encoder = new JsonEncode();
        $this->contextRegisterFactory = $contextRegisterFactory;
    }

    /**
     * @inheritDoc
     */
    public function encode($data, $format, array $context = [])
    {
        $context['register'] = $this->getRegister($context);
        $data = $this->serializer->normalize($data, self::FORMAT, $context);
        $data = [
            'data' => $data,
        ];
        return $this->encoder->encode($data, $format, array_merge($context, [
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        ]));
    }

    /**
     * @inheritDoc
     */
    public function supportsEncoding($format)
    {
        return $format === self::FORMAT ||
               (
                    $this->encoder->supportsEncoding($format) &&
                    self::checkAcceptable($this->requestStack->getMasterRequest())
               );
    }

    private function getRegister(array $context) : RegisterInterface
    {
        if (isset($context['register'])) {
            $register = $context['register'];
        } elseif (
            ($request = $this->requestStack->getMasterRequest()) &&
            ($request->attributes->has('contextRegister'))
        ) {
            $register = $request->attributes->get('contextRegister');
        } else {
            $register = $this->contextRegisterFactory->createContextRegister(null);
        }
        if ($register instanceof RegisterInterface) {
            return $register;
        }
        throw new BadRequestHttpException();
    }

    /**
     * @inheritDoc
     */
    public static function checkAcceptable(?Request $request): bool
    {
        return $request && in_array(self::FORMAT, $request->getAcceptableContentTypes());
    }
}
