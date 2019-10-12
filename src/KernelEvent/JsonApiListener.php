<?php

namespace JsonApi\KernelEvent;

use JsonApi\Exception\InvalidTypeException;
use JsonApi\Exception\ParseUrlException;
use JsonApi\Metadata\ContextRegisterFactory;
use JsonApi\Serializer\Encoder\JsonVndApiEncoder;
use Symfony\Component\Translation\Translator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
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
     * @param Translator             $translator
     * @param ContextRegisterFactory $contextRegisterFactory
     */
    public function __construct(Translator $translator, ContextRegisterFactory $contextRegisterFactory)
    {
        $this->translator             = $translator;
        $this->contextRegisterFactory = $contextRegisterFactory;
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
        if (JsonVndApiEncoder::checkAcceptable($event->getRequest())) {
            $request = $event->getRequest();
            $locale  = $request->getPreferredLanguage($this->translator->getFallbackLocales());
            $request->setLocale($locale);
            $this->translator->setLocale($locale);
            if ($request->isMethod(Request::METHOD_GET)) {
                $fields = $request->query->get('fields');
                $contextRegister  = $this->contextRegisterFactory->createContextRegister($fields);
                $request->attributes->set('contextRegister', $contextRegister);
            }
        }
    }

    public function onKernelException(ExceptionEvent $event)
    {
        if (!JsonVndApiEncoder::checkAcceptable($event->getRequest())) {
            return;
        }
    }
}
