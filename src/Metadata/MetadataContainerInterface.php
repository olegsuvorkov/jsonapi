<?php

namespace JsonApi\Metadata;

use Doctrine\Common\Persistence\ObjectManager;
use JsonApi\Router\ApiUrlGeneratorInterface;
use JsonApi\SecurityStrategy\SecurityStrategyInterface;
use JsonApi\Transformer\TransformerPoolInterface;

/**
 * @package JsonApi\Metadata
 */
interface MetadataContainerInterface extends TransformerPoolInterface, ApiUrlGeneratorInterface
{
    /**
     * @param string $class
     * @return ObjectManager
     */
    public function getEntityManager(string $class): ObjectManager;

    /**
     * @param string $strategy
     * @param array $options
     * @return SecurityStrategyInterface
     */
    public function buildSecurityStrategy(string $strategy, array $options): SecurityStrategyInterface;
}
