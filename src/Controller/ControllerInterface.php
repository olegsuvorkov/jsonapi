<?php

namespace JsonApi\Controller;

use JsonApi\Context\ContextInterface;
use JsonApi\SecurityStrategy\SecurityStrategyInterface;
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
     * @param SecurityStrategyInterface $securityStrategy
     * @return Response
     */
    public function list(
        Request $request,
        ContextInterface $context,
        SecurityStrategyInterface $securityStrategy
    ): Response;

    /**
     * @param string $id
     * @param ContextInterface $context
     * @param SecurityStrategyInterface $securityStrategy
     * @return Response
     */
    public function fetch(
        string $id,
        ContextInterface $context,
        SecurityStrategyInterface $securityStrategy
    ): Response;

    /**
     * @param string $id
     * @param string $relationship
     * @param ContextInterface $context
     * @param SecurityStrategyInterface $securityStrategy
     * @return Response
     */
    public function relationships(
        string $id,
        string $relationship,
        ContextInterface $context,
        SecurityStrategyInterface $securityStrategy
    ): Response;

    /**
     * @param Request $request
     * @param ContextInterface $context
     * @param SecurityStrategyInterface $securityStrategy
     * @return Response
     */
    public function create(
        Request $request,
        ContextInterface $context,
        SecurityStrategyInterface $securityStrategy
    ): Response;
}
