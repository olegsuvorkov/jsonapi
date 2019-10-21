<?php

namespace JsonApi\SecurityStrategy;

use JsonApi\MetadataBuilder\BuilderException;

/**
 * @package JsonApi\SecurityStrategy
 */
interface SecurityStrategyBuilderInterface
{
    /**
     * @return string
     */
    public function getStrategy(): string;

    /**
     * @param array $options
     * @return array
     * @throws BuilderException
     */
    public function configureSecurityStrategy(array $options): array;

    /**
     * @param array $options
     * @return SecurityStrategyInterface
     */
    public function buildSecurityStrategy(array $options): SecurityStrategyInterface;
}
