<?php

namespace JsonApi\SecurityStrategy;

use JsonApi\MetadataBuilder\BuilderException;

/**
 * @package JsonApi\SecurityStrategy
 */
class SecurityStrategyBuilderPool
{
    /**
     * @var SecurityStrategyBuilderInterface[]
     */
    private $strategies = [];

    /**
     * @param SecurityStrategyBuilderInterface[] $strategies
     */
    public function __construct(array $strategies)
    {
        foreach ($strategies as $strategy) {
            $this->strategies[$strategy->getStrategy()] = $strategy;
        }
    }

    /**
     * @param array $options
     * @return array
     * @throws BuilderException
     */
    public function configureSecurity($options): array
    {
        if (is_array($options)) {
            $strategy = 'none';
            $roles    = [];
            foreach ($options as $key => $value) {
                if ($key === 'strategy') {
                    $strategy = $value;
                } elseif ($key === 'roles') {
                    $roles = $value;
                }
            }
            return $this->configureSecurityStrategy($strategy, $roles);
        }
        throw new BuilderException();
    }

    /**
     * @param string $strategy
     * @param array $options
     * @return array
     * @throws BuilderException
     */
    private function configureSecurityStrategy($strategy, $options): array
    {
        if (is_string($strategy)) {
            $strategyBuilder = $this->strategies[$strategy] ?? null;
            if ($strategyBuilder) {
                if (is_array($options)) {
                    return [$strategy, $strategyBuilder->configureSecurityStrategy($options)];
                }
                throw new BuilderException();
            }
            throw new BuilderException('Undefined strategy');
        }
        throw new BuilderException();

    }

    /**
     * @param string $strategy
     * @param array $options
     * @return SecurityStrategyInterface
     */
    public function buildSecurityStrategy(string $strategy, array $options): SecurityStrategyInterface
    {
        return $this->strategies[$strategy]->buildSecurityStrategy($options);
    }
}
