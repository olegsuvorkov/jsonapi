<?php

namespace JsonApi\SecurityStrategy;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @package JsonApi\SecurityStrategy
 */
class RoleSecurityStrategy implements SecurityStrategyInterface
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var array|string[]
     */
    private $roles;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param string[] $roles
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, array $roles)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->roles = $roles;
    }

    /**
     * @param string $attribute
     * @param $subject
     * @return bool
     */
    public function isGranted(string $attribute, $subject = null): bool
    {
        return $this->authorizationChecker->isGranted($this->roles[$attribute]);
    }

    /**
     * @inheritDoc
     */
    public function denyAccessUnlessGranted(string $attribute, $subject = null): void
    {
        if (!$this->authorizationChecker->isGranted($this->roles[$attribute])) {
            $exception = new AccessDeniedException();
            $exception->setAttributes($this->roles[$attribute]);
            throw $exception;
        }
    }
}
