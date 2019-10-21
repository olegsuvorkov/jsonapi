<?php

namespace JsonApi\DependencyInjection\Compiler;

use JsonApi\SecurityStrategy\SecurityStrategyBuilderInterface;
use JsonApi\SecurityStrategy\SecurityStrategyBuilderPool;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @package JsonApi\DependencyInjection\Compiler
 */
class SecurityStrategyCompilerPass implements CompilerPassInterface
{
    private const TAG = 'json_api.security_strategy';

    /**
     * @param ContainerBuilder $container
     */
    public static function registerAutoconfiguration(ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(SecurityStrategyBuilderInterface::class)
            ->addTag(self::TAG);
    }

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $serviceIds = array_keys($container->findTaggedServiceIds(self::TAG));
        $strategies = [];
        foreach ($serviceIds as $serviceId) {
            $strategies[] = new Reference($serviceId);
        }
        $container->getDefinition(SecurityStrategyBuilderPool::class)->replaceArgument(0, $strategies);
    }
}
