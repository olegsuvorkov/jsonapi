<?php

namespace JsonApi;

use JsonApi\DependencyInjection\Compiler\FieldNormalizerCompilerPass;
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
        $container->addCompilerPass(new FieldNormalizerCompilerPass());
    }
}
