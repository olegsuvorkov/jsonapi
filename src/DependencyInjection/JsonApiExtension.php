<?php

namespace JsonApi\DependencyInjection;

use JsonApi\DependencyInjection\Compiler\FieldNormalizerCompilerPass;
use JsonApi\DependencyInjection\Configuration\JsonApiConfiguration;
use JsonApi\FieldNormalizer\FieldNormalizerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Parameter;
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
        $container
            ->registerForAutoconfiguration(FieldNormalizerInterface::class)
            ->addTag(FieldNormalizerCompilerPass::TAG_FIELD_NORMALIZER);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('metadata.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $container->getDefinition('jsonapi.loader.file')->replaceArgument(0, $config['files']);
        $container->getDefinition('jsonapi.loader.cache')->replaceArgument(0, $config['cache_key']);
    }

    /**
     * @inheritDoc
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new JsonApiConfiguration();
    }
}
