<?php

namespace JsonApi\KernelEvent;

use JsonApi\Exception\Exception;
use JsonApi\Context\Context;
use JsonApi\ContextInclude\ContextIncludeBuilder;
use JsonApi\Exception\ParseUrlException;
use JsonApi\Metadata\ContextRegisterFactory;
use JsonApi\Metadata\UndefinedMetadataException;
use JsonApi\Serializer\Encoder\JsonVndApiEncoder;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Translation\Translator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @package Vision\SystemBundle\KernelEvent
 */
class JsonApiListener implements EventSubscriberInterface
{
    /**
     * @var ContextRegisterFactory
     */
    private $contextRegisterFactory;

    /**
     * @var Translator
     */
    private $translator;
    /**
     * @var string
     */
    private $prefix;

    /**
     * @param Translator $translator
     * @param ContextRegisterFactory $contextRegisterFactory
     * @param string $prefix
     */
    public function __construct(Translator $translator, ContextRegisterFactory $contextRegisterFactory, string $prefix)
    {
        $this->translator             = $translator;
        $this->contextRegisterFactory = $contextRegisterFactory;
        $this->prefix                 = $prefix;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST   => 'onKernelRequest',
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    /**
     * @param RequestEvent $event
     * @throws ParseUrlException
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if ($request->attributes->get('exception') instanceof FlattenException) {
            return;
        }
        if (0 !== strncmp($request->getPathInfo(), $this->prefix, strlen($this->prefix))) {
            return;
        }
        if ($request->getPathInfo() === $this->prefix.'schema.js') {
            return;
        }
        if (!in_array(JsonVndApiEncoder::FORMAT, $request->getAcceptableContentTypes())) {
            $event->setResponse(new JsonResponse([
                'errors' => [
                    'status' => Response::HTTP_BAD_REQUEST,
                ]
            ], Response::HTTP_BAD_REQUEST));
            return;
        }
        $locale  = $request->getPreferredLanguage($this->translator->getFallbackLocales());
        $request->setLocale($locale);
        $this->translator->setLocale($locale);
        $fields = $request->query->get('fields');
        $contextRegister  = $this->contextRegisterFactory->createContextRegister($fields);
        $type = $request->attributes->get('type', '');
        try {
            $metadata = $contextRegister->getByType($type);
        } catch (UndefinedMetadataException $e) {
            $event->setResponse(new JsonResponse([
                'errors' => [
                    'title' => 'Undefined type',
                    'status' => Response::HTTP_BAD_REQUEST,

                ]
            ], Response::HTTP_BAD_REQUEST));
            return;
        }
        $contextIncludeBuilder = new ContextIncludeBuilder($contextRegister, $type);
        $contextInclude = $contextIncludeBuilder->build($request->query->get('include', ''));
        $request->attributes->set('context', new Context($metadata, $contextInclude, $contextRegister));
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $request = $event->getRequest();
        if (!in_array(JsonVndApiEncoder::FORMAT, $request->getAcceptableContentTypes())) {
            return;
        }
        $exception = $event->getException();
        if ($exception instanceof BadRequestHttpException || $exception instanceof Exception) {
            $response = new JsonResponse([
                'errors' => [
                    'status' => $exception->getStatusCode(),
                    'title' => $exception->getMessage(),
                ]
            ]);
            $event->setResponse($response);
        }
    }
}
