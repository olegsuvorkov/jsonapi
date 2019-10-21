<?php

namespace JsonApi\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use JsonApi\Context\ContextInterface;
use JsonApi\Metadata\MetadataInterface;
use JsonApi\SecurityStrategy\SecurityStrategyInterface;
use JsonApi\Serializer\Encoder\JsonVndApiEncoder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as OriginalAbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vision\SystemBundle\Repository\FilterRepositoryInterface;

/**
 * @package JsonApi\Controller
 */
abstract class AbstractController extends OriginalAbstractController implements ControllerInterface
{
    /**
     * @inheritDoc
     */
    public function list(
        Request $request,
        ContextInterface $context,
        SecurityStrategyInterface $securityStrategy
    ): Response {
        $securityStrategy->denyAccessUnlessGranted('list');
        $list = $this->getList($request, $context);
        $data = $this->serialize($list, $context);
        return $this->json($data);
    }

    /**
     * @inheritDoc
     */
    public function fetch(
        string $id,
        ContextInterface $context,
        SecurityStrategyInterface $securityStrategy
    ): Response {
        $metadata = $context->getMetadata();
        $repository = $this->getRepository($metadata);
        $item = $repository->find($metadata->reverseId($id));
        $securityStrategy->denyAccessUnlessGranted('view', $item);
        if ($item) {
            $data = $this->serialize($item, $context);
            return $this->json($data);
        }
        throw new NotFoundHttpException();
    }

    /**
     * @inheritDoc
     */
    public function relationships(
        string $id,
        string $relationship,
        ContextInterface $context,
        SecurityStrategyInterface $securityStrategy
    ): Response {
        $metadata = $context->getMetadata();
        $em = $this->getEntityManager($metadata);
        $item = $em->find($metadata->getClass(), $metadata->reverseId($id));
        if ($item === null) {
            throw new NotFoundHttpException();
        }
        $securityStrategy->denyAccessUnlessGranted('view', $item);
        $field = $metadata->getOriginalMetadata($item)->getRelationship($relationship);
        /** @var MetadataInterface $targetMetadata */
        $targetMetadata = $field->getOption('target');
        $targetMetadata->getSecurity()->denyAccessUnlessGranted('list');
        if ($field === null) {
            throw new NotFoundHttpException();
        }
        $data = $field->getValue($item);
        $data = $this->serialize($data, $context);
        return $this->json($data);
    }

    public function create(
        Request $request,
        ContextInterface $context,
        SecurityStrategyInterface $securityStrategy
    ): Response {
        $entity = $this->deserialize($request->getContent(), $context);
        $securityStrategy->denyAccessUnlessGranted('create', $entity);
        $metadata = $context->getMetadata();
        $em = $this->getEntityManager($metadata);
        $em->persist($entity);
        $em->flush();
        $data = $this->serialize($entity, $context);
        $namePrefix = $this->getParameter('json_api_name_prefix');

        $url = $this->generateUrl($namePrefix.$metadata->getType().'_get', [
            'id' => $metadata->getId($entity),
        ]);
        return $this->json($data, Response::HTTP_CREATED, [
            'Location' => $url,
            'Content-Type' => JsonVndApiEncoder::FORMAT,
        ]);
    }

    protected function getList(Request $request, ContextInterface $context)
    {
        $repository = $this->getRepository($context->getMetadata());
        if (($repository instanceof FilterRepositoryInterface) &&
            ($form = $this->createFilter())
        ) {
            $filter = $request->query->get('filter', []);
            if (!is_array($filter)) {
                throw new BadRequestHttpException();
            }
            $form->submit($filter);
            $criteria = $form->getData();
            return $repository->findByCriteria($criteria);
        } else {
            return $repository->findAll();
        }
    }

    protected function createFilter(): ?FormInterface
    {
        return null;
    }

    /**
     * @param $type
     * @param null $data
     * @param array $options
     * @return FormInterface
     */
    protected function createFilterForm($type, $data = null, array $options = [])
    {
        /** @var FormFactoryInterface $formFactory */
        $formFactory = $this->get('form.factory');
        return $formFactory->createNamed('filter', $type, $data, $options);
    }

    /**
     * @inheritDoc
     */
    protected function serialize($data, ContextInterface $context): string
    {
        return $this->container->get('serializer')->serialize($data, JsonVndApiEncoder::FORMAT, [
            'context' => $context,
        ]);
    }

    protected function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers, true);
    }

    /**
     * @inheritDoc
     */
    protected function deserialize($data, ContextInterface $context)
    {
        $type = $context->getMetadata()->getType();
        return $this->container->get('serializer')->deserialize($data, $type, JsonVndApiEncoder::FORMAT);
    }

    protected function getContext(): ContextInterface
    {
        /** @var RequestStack $requestStack */
        $requestStack = $this->get('request_stack');
        $request = $requestStack->getMasterRequest();
        return $request->attributes->get('context');
    }
    /**
     * @param MetadataInterface $metadata
     * @return ObjectManager
     */
    protected function getEntityManager(MetadataInterface $metadata = null): ObjectManager
    {
        $metadata = $metadata ?? $this->getContext()->getMetadata();
        return $this->getDoctrine()->getManagerForClass($metadata->getClass());
    }

    /**
     * @param MetadataInterface $metadata
     * @return ObjectRepository
     */
    protected function getRepository(MetadataInterface $metadata = null): ObjectRepository
    {
        $metadata = $metadata ?? $this->getContext()->getMetadata();
        return $this->getEntityManager($metadata)->getRepository($metadata->getClass());
    }
}
