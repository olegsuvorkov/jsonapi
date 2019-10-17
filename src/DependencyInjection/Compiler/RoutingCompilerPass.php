<?php

namespace JsonApi\DependencyInjection\Compiler;

use JsonApi\Router\RouteLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @package JsonApi\DependencyInjection\Compiler
 */
class RoutingCompilerPass implements CompilerPassInterface
{
    const TAG_CONTROLLER = 'json_api.controller';

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $loaderDefinition = $container->getDefinition(RouteLoader::class);
        $serviceIds = array_keys($container->findTaggedServiceIds(self::TAG_CONTROLLER));
        $controllerDefinitions = [];
        foreach ($serviceIds as $serviceId) {
            $controllerDefinitions[] = new Reference($serviceId);
        }
        $loaderDefinition->replaceArgument(2, $controllerDefinitions);
    }
}
