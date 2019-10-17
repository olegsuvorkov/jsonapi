<?php

namespace JsonApi\Controller;

use JsonApi\Serializer\Encoder\JsonVndApiEncoder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as OriginalAbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @package JsonApi\Controller
 */
abstract class AbstractController extends OriginalAbstractController implements ControllerInterface
{

    /**
     * @inheritDoc
     */
    protected function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        if ($this->container->has('serializer')) {
            /** @var RequestStack $requestStack */
            $requestStack = $this->get('request_stack');
            $request = $requestStack->getMasterRequest();
            $context['context'] = $request->attributes->get('context');
            $json = $this->container->get('serializer')->serialize($data, JsonVndApiEncoder::FORMAT, $context);

            return new JsonResponse($json, $status, $headers, true);
        }

        return new JsonResponse($data, $status, $headers);
    }

}
