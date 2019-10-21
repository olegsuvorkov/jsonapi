<?php

namespace JsonApi\SecurityStrategy;

/**
 * @package JsonApi\SecurityStrategy
 */
class SubjectRoleSecurityStrategyBuilder extends RoleSecurityStrategyBuilder
{
    /**
     * @inheritDoc
     */
    public function getStrategy(): string
    {
        return 'subject_role';
    }

    /**
     * @inheritDoc
     */
    public function buildSecurityStrategy(array $options): SecurityStrategyInterface
    {
        return new SubjectRoleSecurityStrategy($this->authorizationChecker, $options);
    }
}
