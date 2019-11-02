<?php

namespace JsonApi\DependencyInjection;

use JsonApi\DependencyInjection\Compiler\RoutingCompilerPass;
use JsonApi\DependencyInjection\Compiler\SecurityStrategyCompilerPass;
use JsonApi\DependencyInjection\Compiler\TransformerCompilerPass;
use JsonApi\DependencyInjection\Configuration\JsonApiConfiguration;
use JsonApi\KernelEvent\JsonApiListener;
use JsonApi\Loader\CacheLoader;
use JsonApi\Loader\ParserLoader;
use JsonApi\Metadata\LoaderRegister;
use JsonApi\Parser\YamlParser;
use JsonApi\Router\RouteLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @package JsonApi\DependencyInjection
 */
class JsonApiExtension extends Extension
{
    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        RoutingCompilerPass::registerAutoconfiguration($container);
        SecurityStrategyCompilerPass::registerAutoconfiguration($container);
        TransformerCompilerPass::registerAutoconfiguration($container);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('metadata.xml');
        $loader->load('serializer.xml');
        $loader->load('routing.xml');
        $projectDir = $container->getParameter('kernel.project_dir').'/config';
        $locator = new FileLocator($projectDir);
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $parserList = [];
        foreach ($config['files'] as $file) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if ($extension === 'yaml' || $extension === 'yml') {
                $file = $locator->locate($file);
                if (is_array($file)) {
                    $file = reset($file);
                }
                $parserList[] = new Definition(YamlParser::class, [$file]);
            }
        }
        $container->getDefinition(ParserLoader::class)->replaceArgument(0, $parserList);
        if ($config['cache_key']) {
            $cacheLoaderDefinition = $container->getDefinition(CacheLoader::class);
            $cacheLoaderDefinition->replaceArgument(0, $config['cache_key']);
            $container->getDefinition(LoaderRegister::class)->replaceArgument(0, $cacheLoaderDefinition);
        }
        $container->setParameter('json_api_name_prefix', $config['name_prefix']);
        $pathPrefixes = parse_url($config['path_prefix']);
        $path = $pathPrefixes['path'] ?? '/';
        $path = $path ?: '/';
        $container->setParameter('json_api_path_prefix', $path);
        $container
            ->getDefinition(RouteLoader::class)
            ->replaceArgument(1, $pathPrefixes['scheme'] ?? null)
            ->replaceArgument(2, $pathPrefixes['host'] ?? null)
            ->replaceArgument(3, $path)
            ->replaceArgument(4, $config['name_prefix']);

        $container->getDefinition(JsonApiListener::class)->replaceArgument(2, $path);
    }

    /**
     * @inheritDoc
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new JsonApiConfiguration();
    }
}
