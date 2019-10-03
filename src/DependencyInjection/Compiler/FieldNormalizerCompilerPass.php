<?php


namespace JsonApi\DependencyInjection\Compiler;


use JsonApi\FieldNormalizer\Pool;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FieldNormalizerCompilerPass implements CompilerPassInterface
{
    const TAG_FIELD_NORMALIZER = 'json_api.field_normalizer';

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->has(Pool::class)) {
            $poolDefinition = $container->getDefinition(Pool::class);
            $serviceIds = array_keys($container->findTaggedServiceIds(self::TAG_FIELD_NORMALIZER));
            foreach ($serviceIds as $serviceId) {
                $poolDefinition->addMethodCall(
                    'add', [new Reference($serviceId)]
                );
            }
        }
    }
}