<?php

namespace JsonApi\KernelEvent\KernelEvent;

use JsonApi\Context\ContextFactory;
use JsonApi\Context\ContextFactoryInterface;
use JsonApi\Controller\Controller;
use JsonApi\CriteriaFactory\CriteriaFactoryInterface;
use JsonApi\CriteriaFactory\PrefixCriteriaFactory;
use JsonApi\Exception\InvalidTypeException;
use JsonApi\Exception\ParseUrlException;
use JsonApi\Metadata\Register;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @package Vision\SystemBundle\KernelEvent
 */
class JsonApiListener implements EventSubscriberInterface
{
    /**
     * @var ContextFactoryInterface
     */
    private $contextFactory;

    /**
     * @var CriteriaFactoryInterface
     */
    private $criteriaFactory;

    public function __construct()
    {
        $this->contextFactory  = new ContextFactory(new Register());
        $this->criteriaFactory = new PrefixCriteriaFactory();
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    /**
     * @param RequestEvent $event
     * @throws InvalidTypeException
     * @throws ParseUrlException
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if ($this->checkAccept($event)) {
            $request = $event->getRequest();
            if ($request->isMethod(Request::METHOD_GET)) {
                $context  = $this->contextFactory->createContext(
                    $request->attributes->get('type'),
                    $request->query->get('include'),
                    $request->query->get('fields')
                );
                $criteria = $this->criteriaFactory->createCriteria(
                    $context,
                    $this->getFilter($request),
                    $request->query->get('sort'),
                    $request->query->get('page')
                );
                $request->attributes->set('context', $context);
                $request->attributes->set('criteria', $criteria);
            }
        }
    }

    public function onKernelException(ExceptionEvent $event)
    {
        if (!$this->checkAccept($event)) {
            return;
        }
    }

    private function checkAccept(KernelEvent $event)
    {
        return in_array(Controller::MIME_TYPE, $event->getRequest()->getAcceptableContentTypes());
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getFilter(Request $request): string
    {
        $queryString = $request->server->get('QUERY_STRING');
        if (0 === strncmp($queryString, 'filter=', 7)) {
            $position = 7;
        } elseif (false !== ($position = strpos($queryString, '&filter='))) {
            $position+= 8;
        }
        $end = strpos($queryString, '&', $position);
        if ($end === false) {
            $end = strlen($queryString);
        }
        return substr($queryString, $position, $end - $position);
    }
}
