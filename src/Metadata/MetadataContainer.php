<?php

namespace JsonApi\Metadata;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use JsonApi\Router\ApiUrlGeneratorInterface;
use JsonApi\Router\RouteLoader;
use JsonApi\SecurityStrategy\SecurityStrategyBuilderPool;
use JsonApi\SecurityStrategy\SecurityStrategyInterface;
use JsonApi\Transformer\TransformerPoolInterface;
use JsonApi\Transformer\TransformerInterface;

/**
 * @package JsonApi\Metadata
 */
class MetadataContainer implements MetadataContainerInterface
{
    /**
     * @var SecurityStrategyBuilderPool
     */
    private $securityBuilder;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;
    /**
     * @var TransformerPoolInterface
     */
    private $transformerPool;
    /**
     * @var RouteLoader
     */
    private $urlGenerator;

    /**
     * @param SecurityStrategyBuilderPool $securityBuilder
     * @param ManagerRegistry $managerRegistry
     * @param TransformerPoolInterface $transformerPool
     * @param ApiUrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        SecurityStrategyBuilderPool $securityBuilder,
        ManagerRegistry $managerRegistry,
        TransformerPoolInterface $transformerPool,
        ApiUrlGeneratorInterface $urlGenerator
    ) {
        $this->securityBuilder = $securityBuilder;
        $this->managerRegistry = $managerRegistry;
        $this->transformerPool = $transformerPool;
        $this->urlGenerator    = $urlGenerator;
    }

    /**
     * @inheritDoc
     */
    public function getEntityManager(string $class): ObjectManager
    {
        return $this->managerRegistry->getManagerForClass($class);
    }

    /**
     * @param string $strategy
     * @param array $options
     * @return SecurityStrategyInterface
     */
    public function buildSecurityStrategy(string $strategy, array $options): SecurityStrategyInterface
    {
        return $this->securityBuilder->buildSecurityStrategy($strategy, $options);
    }

    /**
     * @inheritDoc
     */
    public function getTransformer(string $type): TransformerInterface
    {
        return $this->transformerPool->getTransformer($type);
    }

    /**
     * @inheritDoc
     */
    public function generateUrl(string $type): string
    {
        return $this->urlGenerator->generateUrl($type);
    }

    /**
     * @inheritDoc
     */
    public function generateEntityUrl(string $type, string $id): string
    {
        return $this->urlGenerator->generateEntityUrl($type, $id);
    }

    /**
     * @inheritDoc
     */
    public function generateRelationshipUrl(string $type, string $id, string $name): string
    {
        return $this->urlGenerator->generateRelationshipUrl($type, $id, $name);
    }
}
