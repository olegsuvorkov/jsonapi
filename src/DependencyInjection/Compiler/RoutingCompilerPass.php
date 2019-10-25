<?php

namespace JsonApi\DependencyInjection\Compiler;

use JsonApi\Controller\ControllerInterface;
use JsonApi\Router\RouteLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @package JsonApi\DependencyInjection\Compiler
 */
class RoutingCompilerPass implements CompilerPassInterface
{
    private const TAG = 'json_api.controller';

    /**
     * @param ContainerBuilder $container
     */
    public static function registerAutoconfiguration(ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(ControllerInterface::class)
            ->addTag(self::TAG);
    }

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $loaderDefinition = $container->getDefinition(RouteLoader::class);
        $serviceIds = array_keys($container->findTaggedServiceIds(self::TAG));
        $controllerDefinitions = [];
        foreach ($serviceIds as $serviceId) {
            $controllerDefinitions[$serviceId] = new Reference($serviceId);
        }
        $loaderDefinition->replaceArgument(5, $controllerDefinitions);
    }
}
