<?php

namespace JsonApi\Controller;

use JsonApi\Context\ContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package JsonApi\Controller
 */
interface ControllerInterface
{
    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param Request $request
     * @param ContextInterface $context
     * @return Response
     */
    public function list(Request $request, ContextInterface $context): Response;

    /**
     * @param string $id
     * @param ContextInterface $context
     * @return Response
     */
    public function fetch(string $id, ContextInterface $context): Response;

    /**
     * @param string $id
     * @param string $relationship
     * @param ContextInterface $context
     * @return Response
     */
    public function relationships(string $id, string $relationship, ContextInterface $context): Response;

    /**
     * @param Request $request
     * @param string $id
     * @param string $relationship
     * @param ContextInterface $context
     * @return Response
     */
    public function relationshipsDelete(
        Request $request,
        string $id,
        string $relationship,
        ContextInterface $context
    ): Response;

    /**
     * @param Request $request
     * @param ContextInterface $context
     * @return Response
     */
    public function create(Request $request, ContextInterface $context): Response;
}
