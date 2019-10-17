<?php

namespace JsonApi\DependencyInjection\Compiler;

use JsonApi\Metadata\LoaderRegister;
use JsonApi\MetadataBuilder\Configurator\AttributeConfigurator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @package JsonApi\DependencyInjection\Compiler
 */
class TransformerCompilerPass implements CompilerPassInterface
{
    const TAG_TRANSFORMER              = 'json_api.transformer';
    const TAG_TRANSFORMER_CONFIGURATOR = 'json_api.transformer_configurator';

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $serviceIds = array_keys($container->findTaggedServiceIds(self::TAG_TRANSFORMER));
        $transformers = [];
        foreach ($serviceIds as $serviceId) {
            $transformers[] = new Reference($serviceId);
        }
        $container->getDefinition(LoaderRegister::class)->replaceArgument(1, $transformers);
        $attributes = [];
        foreach ($container->findTaggedServiceIds(self::TAG_TRANSFORMER_CONFIGURATOR) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                if (isset($tag['type'])) {
                    $attributes[$tag['type']] = new Reference($serviceId);
                } else {
                    throw new \InvalidArgumentException(sprintf(
                        'The name is not defined in the "%s" tag for the service "%s"',
                        self::TAG_TRANSFORMER_CONFIGURATOR,
                        $serviceId
                    ));
                }
            }
        }
        $attributesTransformer = $container->getDefinition(AttributeConfigurator::class);
        $attributesTransformer->replaceArgument(0, $attributes);
    }
}
