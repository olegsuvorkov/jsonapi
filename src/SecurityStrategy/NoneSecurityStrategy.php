<?php

namespace JsonApi\SecurityStrategy;

/**
 * @package JsonApi\SecurityStrategy
 */
class NoneSecurityStrategy implements SecurityStrategyInterface
{
    /**
     * @inheritDoc
     */
    public function isGranted(string $attribute, $subject = null): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function denyAccessUnlessGranted(string $attribute, $subject = null): void
    {
    }
}
