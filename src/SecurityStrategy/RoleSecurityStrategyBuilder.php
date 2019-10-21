<?php

namespace JsonApi\SecurityStrategy;

use JsonApi\MetadataBuilder\BuilderException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RoleSecurityStrategyBuilder implements SecurityStrategyBuilderInterface
{
    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    private $defaults = [
        'list' => null,
        'view' => null,
        'update' => null,
        'create' => null,
        'delete' => null,
    ];

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @inheritDoc
     */
    public function getStrategy(): string
    {
        return 'role';
    }


    /**
     * @inheritDoc
     */
    public function configureSecurityStrategy(array $options): array
    {
        $data = [];
        foreach (array_merge($this->defaults, $options) as $key => $value) {
            if ($key === 'list' || $key === 'view' || $key === 'update' || $key === 'create' || $key === 'delete') {
                if (is_string($value)) {
                    $data[$key] = $value;
                } else {
                    throw new BuilderException(sprintf('Invalid security key `%s` expected string', $key));
                }
            } else {
                throw new BuilderException(sprintf('Undefined security key `%s`', $key));
            }
        }
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function buildSecurityStrategy(array $options): SecurityStrategyInterface
    {
        return new RoleSecurityStrategy($this->authorizationChecker, $options);
    }
}
