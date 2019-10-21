<?php

namespace JsonApi\DependencyInjection\Compiler;

use JsonApi\Controller\ControllerInterface;
use JsonApi\Metadata\LoaderRegister;
use JsonApi\Metadata\RegisterFactory;
use JsonApi\MetadataBuilder\Configurator\AttributeConfigurator;
use JsonApi\Transformer\TransformerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @package JsonApi\DependencyInjection\Compiler
 */
class TransformerCompilerPass implements CompilerPassInterface
{
    private const TAG = 'json_api.transformer';


    /**
     * @param ContainerBuilder $container
     */
    public static function registerAutoconfiguration(ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(TransformerInterface::class)
            ->addTag(self::TAG);
    }

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $serviceIds = array_keys($container->findTaggedServiceIds(self::TAG));
        $transformers = [];
        foreach ($serviceIds as $serviceId) {
            $transformers[] = new Reference($serviceId);
        }
        $container->getDefinition(RegisterFactory::class)->replaceArgument(2, $transformers);
        $attributes = [];
        foreach ($this->findTaggedServiceMap($container, 'json_api.transformer_configurator') as $type => $serviceId) {
            $attributes[$type] = new Reference($serviceId);
        }
        $container->getDefinition(AttributeConfigurator::class)->replaceArgument(0, $attributes);
    }

    private function findTaggedServiceMap(ContainerBuilder $container, string $name): array
    {
        $list = [];
        foreach ($container->findTaggedServiceIds($name) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                if (isset($tag['type'])) {
                    $list[$tag['type']] = $serviceId;
                } else {
                    throw new \InvalidArgumentException(sprintf(
                        'The name is not defined in the "%s" tag for the service "%s"',
                        $name,
                        $serviceId
                    ));
                }
            }
        }
        return $list;
    }
}
