<?php

namespace JsonApi\DependencyInjection\Compiler;

use JsonApi\Normalizer\Serializer;
use JsonApi\Normalizer\TypeNormalizerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @package JsonApi\DependencyInjection\Compiler
 */
class NormalizerCompilerPass implements CompilerPassInterface
{
    private const TAG = 'json_api.normalizer';

    /**
     * @param ContainerBuilder $container
     */
    public static function registerAutoconfiguration(ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(TypeNormalizerInterface::class)
            ->addTag(self::TAG);
    }

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $attributes = $this->findTaggedServiceMap($container);
        $container->getDefinition(Serializer::class)->replaceArgument(0, $attributes);
    }

    private function findTaggedServiceMap(ContainerBuilder $container): array
    {
        $list = [];
        foreach ($container->findTaggedServiceIds(self::TAG) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $type = $tag['type'] ?? null;
                if ($type) {
                    $list[$type] = new Reference($serviceId);
                } else {
                    $list[] = new Reference($serviceId);
                }
            }
        }
        return $list;
    }
}
