<?php

namespace JsonApi;

use JsonApi\DependencyInjection\Compiler\RoutingCompilerPass;
use JsonApi\DependencyInjection\Compiler\SecurityStrategyCompilerPass;
use JsonApi\DependencyInjection\Compiler\TransformerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @package JsonApi
 */
class JsonApiBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TransformerCompilerPass());
        $container->addCompilerPass(new RoutingCompilerPass());
        $container->addCompilerPass(new SecurityStrategyCompilerPass());
    }
}
