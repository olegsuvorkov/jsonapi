<?php

namespace JsonApi\Controller;

use Doctrine\Common\Collections\Collection;
use JsonApi\Context\ContextInterface;
use JsonApi\Serializer\Encoder\JsonVndApiEncoder;
use JsonApi\Transformer\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as OriginalAbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
    public function list(Request $request, ContextInterface $context): Response
    {
        $context->getMetadata()->denyAccessUnlessGranted('list');
        $list = $this->getList($request, $context);
        return $this->json($list, Response::HTTP_OK, [], [
            'context' => $context,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function fetch(string $id, ContextInterface $context): Response
    {
        $metadata = $context->getMetadata();
        $item = $metadata->find($id);
        $metadata->denyAccessUnlessGranted('view', $item);
        if (!$item) {
            throw new NotFoundHttpException();
        }
        return $this->json($item, Response::HTTP_OK, [], [
            'context' => $context,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function relationships(string $id, string $relationship, ContextInterface $context): Response
    {
        $metadata = $context->getMetadata()->getOriginal();
        $item = $metadata->find($id);
        $metadata->denyAccessUnlessGranted('view', $item);
        $field = $metadata->getOriginalMetadata($item)->getRelationship($relationship);
        $field->denyAccessUnlessGranted('list');
        return $this->json($field->getValue($item), Response::HTTP_OK, [], [
            'context' => $context,
            'relationship' => $field,
            'only_identity' => true,
        ]);
    }

    /**
     * @inheritDoc
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
        return $this->json($relationships->toArray(), Response::HTTP_OK, [], [
            'context' => $context,
            'relationship' => $field,
            'only_identity' => true,
        ]);
    }

    public function create(Request $request, ContextInterface $context): Response
    {
        $entity = $this->deserialize($request->getContent(), $context, [
            'allow_create' => true,
        ]);
        $metadata = $context->getMetadata();
        $metadata->denyAccessUnlessGranted('create', $entity);
        $em = $metadata->getEntityManager();
        $em->persist($entity);
        $em->flush();
        return $this->json($entity, Response::HTTP_CREATED, [
            'Location' => $metadata->generateEntityUrl($entity),
            'Content-Type' => JsonVndApiEncoder::FORMAT,
        ], [
            'context' => $context,
        ]);
    }

    public function patch(Request $request, string $id, ContextInterface $context): Response
    {
        $metadata = $context->getMetadata();
        $item = $metadata->find($id);
        $metadata->denyAccessUnlessGranted('update', $item);
        $entity = $this->deserialize($request->getContent(), $context, [
            'allow_create' => true,
        ]);
        if ($entity !== $item) {
            throw new BadRequestHttpException();
        }
        $em = $metadata->getEntityManager();
        $em->persist($entity);
        $em->flush();
        return $this->json($entity, Response::HTTP_CREATED, [
            'Location' => $metadata->generateEntityUrl($entity),
            'Content-Type' => JsonVndApiEncoder::FORMAT,
        ], [
            'context' => $context,
        ]);
    }

    public function delete(string $id, ContextInterface $context)
    {
        $metadata = $context->getMetadata();
        $item = $metadata->find($id);
        $metadata->denyAccessUnlessGranted('delete', $item);
        $em = $metadata->getEntityManager();
        $em->remove($item);
        $em->flush();
        return $this->json('', Response::HTTP_NO_CONTENT);
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
     */
    protected function serialize($data, array $context = []): string
    {
        return $this->get('serializer')->serialize($data, JsonVndApiEncoder::FORMAT, $context);
    }

    /**
     * @inheritDoc
     */
    protected function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        $headers['Content-Type'] = JsonVndApiEncoder::FORMAT;
        return new JsonResponse(is_string($data) ? $data : $this->serialize($data, $context), $status, $headers, true);
    }

    /**
     * @inheritDoc
     */
    protected function deserialize($data, ContextInterface $context, array $params)
    {
        $type = $context->getMetadata()->getType();
        return $this->get('serializer')->deserialize($data, $type, JsonVndApiEncoder::FORMAT, $params);
    }
}
