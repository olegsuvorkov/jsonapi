<?php

namespace JsonApi\SecurityStrategy;

/**
 * @package JsonApi\SecurityStrategy
 */
interface SecurityStrategyInterface
{
    /**
     * @param string $attribute
     * @param $subject
     * @return bool
     */
    public function isGranted(string $attribute, $subject = null): bool;

    /**
     * @param string $attribute
     * @param $subject
     */
    public function denyAccessUnlessGranted(string $attribute, $subject = null): void;
}
