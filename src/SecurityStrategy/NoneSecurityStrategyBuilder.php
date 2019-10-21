<?php

namespace JsonApi\SecurityStrategy;

/**
 * @package JsonApi\SecurityStrategy
 */
class NoneSecurityStrategyBuilder implements SecurityStrategyBuilderInterface
{
    /**
     * @inheritDoc
     */
    public function getStrategy(): string
    {
        return 'none';
    }

    /**
     * @inheritDoc
     */
    public function configureSecurityStrategy(array $options): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function buildSecurityStrategy(array $options): SecurityStrategyInterface
    {
        return new NoneSecurityStrategy();
    }
}
