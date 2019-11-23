<?php

namespace JsonApi\Controller;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use JsonApi\Context\ContextInterface;
use JsonApi\Metadata\UndefinedMetadataException;
use JsonApi\Normalizer\SerializerInterface;
use JsonApi\Transformer\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as OriginalAbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Vision\SystemBundle\Repository\FilterRepositoryInterface;

/**
 * @package JsonApi\Controller
 */
abstract class AbstractController extends OriginalAbstractController implements ControllerInterface
{
    /**
     * @param Request $request
     * @param ContextInterface $context
     * @return Response
     * @throws UndefinedMetadataException
     */
    public function list(Request $request, ContextInterface $context): Response
    {
        $context->getMetadata()->denyAccessUnlessGranted('list');
        $list = $this->getList($request, $context);
        return $this->serialize($list, Response::HTTP_OK, [], [
            'attributes' => true,
            'relationships' => true,
        ]);
    }

    /**
     * @param string $id
     * @param ContextInterface $context
     * @return Response
     * @throws UndefinedMetadataException
     */
    public function fetch(string $id, ContextInterface $context): Response
    {
        $metadata = $context->getMetadata();
        $item = $metadata->find($id);
        $metadata->denyAccessUnlessGranted('view', $item);
        if (!$item) {
            throw new NotFoundHttpException();
        }
        return $this->serialize($item, Response::HTTP_OK, [], [
            'attributes' => true,
            'relationships' => true,
        ]);
    }

    /**
     * @param string $id
     * @param string $relationship
     * @param ContextInterface $context
     * @return Response
     * @throws UndefinedMetadataException
     */
    public function relationships(string $id, string $relationship, ContextInterface $context): Response
    {
        $metadata = $context->getMetadata()->getOriginal();
        $item = $metadata->find($id);
        $metadata->denyAccessUnlessGranted('view', $item);
        $field = $metadata->getOriginalMetadata($item)->getRelationship($relationship);
        $field->denyAccessUnlessGranted('list');
        return $this->serialize($field->getValue($item), Response::HTTP_OK, [], [
            'relationship' => $field,
            'attributes' => false,
            'relationships' => false,
        ]);
    }

    /**
     * @param Request $request
     * @param string $id
     * @param string $relationship
     * @param ContextInterface $context
     * @return Response
     * @throws InvalidArgumentException
     * @throws UndefinedMetadataException
     */
    public function relationshipsDelete(
        Request $request,
        string $id,
        string $relationship,
        ContextInterface $context
    ): Response {
        $metadata = $context->getMetadata()->getOriginal();
        $item = $metadata->find($id);
        $metadata->denyAccessUnlessGranted('view', $item);
        $field = $metadata->getRelationshipByEntity($item, $relationship);
        $field->denyAccessUnlessGranted('list');
        $data = json_decode($request->getContent(), true);
        /** @var Collection $relationships */
        $relationships = $field->reverseTransform($data);
        $em = $metadata->getEntityManager();
        if ($field->isOneToMany()) {
            foreach ($relationships as $relationship) {
                $em->remove($relationship);
            }
        } elseif ($field->isManyToMany()) {
            /** @var Collection $value */
            $value = $field->getValue($item);
            foreach ($relationships as $relationship) {
                $value->removeElement($relationship);
            }
            $em->persist($item);
        } else {
            throw new InvalidArgumentException();
        }
        $em->flush();
        return $this->serialize($relationships->toArray(), Response::HTTP_OK, [], [
            'context' => $context,
            'relationship' => $field,
            'attributes' => false,
            'relationships' => false,
        ]);
    }

    /**
     * @param Request $request
     * @param ContextInterface $context
     * @return Response
     * @throws UndefinedMetadataException
     */
    public function create(Request $request, ContextInterface $context): Response
    {
        $entity = $this->deserialize($request, ['allow_create' => true]);
        $metadata = $context->getMetadata();
        $metadata->denyAccessUnlessGranted('create', $entity);
        $em = $metadata->getEntityManager();
        $em->persist($entity);
        $em->flush();
        return $this->serialize($entity, Response::HTTP_CREATED, [
            'Location' => $metadata->generateEntityUrl($entity),
            'Content-Type' => SerializerInterface::FORMAT,
        ], [
            'attributes' => true,
            'relationships' => true,
        ]);
    }

    /**
     * @param Request $request
     * @param string $id
     * @param ContextInterface $context
     * @return Response
     * @throws UndefinedMetadataException
     */
    public function patch(Request $request, string $id, ContextInterface $context): Response
    {
        $metadata = $context->getMetadata();
        $item = $metadata->find($id);
        $metadata->denyAccessUnlessGranted('update', $item);
        $entity = $this->deserialize($request, ['allow_create' => true]);
        if ($entity !== $item) {
            throw new BadRequestHttpException();
        }
        $em = $metadata->getEntityManager();
        $em->persist($entity);
        $em->flush();
        return $this->serialize($entity, Response::HTTP_CREATED, [
            'Location' => $metadata->generateEntityUrl($entity),
            'Content-Type' => SerializerInterface::FORMAT,
        ], [
            'context' => $context,
            'attributes' => true,
            'relationships' => true,
        ]);
    }

    /**
     * @param string $id
     * @param ContextInterface $context
     * @return Response
     * @throws UndefinedMetadataException
     */
    public function delete(string $id, ContextInterface $context)
    {
        $metadata = $context->getMetadata();
        $item = $metadata->find($id);
        $metadata->denyAccessUnlessGranted('delete', $item);
        $em = $metadata->getEntityManager();
        $em->remove($item);
        $em->flush();
        return $this->serialize('', Response::HTTP_NO_CONTENT, [], [
            'attributes' => true,
            'relationships' => true,
        ]);
    }

    protected function getList(Request $request, ContextInterface $context)
    {
        $repository = $context->getRepository();
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
     * @inheritDoc
     * @throws UndefinedMetadataException
     */
    protected function serialize($data, int $status = 200, array $headers = [], array $options = []): Response
    {
        $data = $this->getSerializer()->serialize($data, $options, $this->getContext());
        $headers['Content-Type'] = SerializerInterface::FORMAT;
        return new JsonResponse($data, $status, $headers, true);
    }

    /**
     * @inheritDoc
     */
    protected function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        $headers['Content-Type'] = SerializerInterface::FORMAT;
        if (is_string($data)) {
            return new JsonResponse($data, $status, $headers, true);
        } else {
            return (new JsonResponse('', $status, $headers))
                ->setEncodingOptions(JsonResponse::DEFAULT_ENCODING_OPTIONS)
                ->setData($data);
        }
    }

    /**
     * @inheritDoc
     */
    protected function deserialize(Request $request, array $options = [])
    {
        /** @var ContextInterface $context */
        $context = $request->attributes->get('context');
        return $this->getSerializer()->deserialize($request, $options, $context);
    }

    protected function getContext(): ContextInterface
    {
        /** @var RequestStack $requestStack */
        $requestStack = $this->get('request_stack');
        /** @var Request $request */
        $request = $requestStack->getCurrentRequest();
        return $request->attributes->get('context');
    }

    protected function getSerializer(): SerializerInterface
    {
        return $this->get('serializer');
    }

    public static function getSubscribedServices()
    {
        return [
            'request_stack' => '?'.RequestStack::class,
            'serializer' => '?'.SerializerInterface::class,
            'security.authorization_checker' => '?'.AuthorizationCheckerInterface::class,
            'doctrine' => '?'.ManagerRegistry::class,
            'form.factory' => '?'.FormFactoryInterface::class,
            'security.token_storage' => '?'.TokenStorageInterface::class,
            'parameter_bag' => '?'.ContainerBagInterface::class,
        ];
    }
}
