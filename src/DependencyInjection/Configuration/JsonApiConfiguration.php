<?php


namespace JsonApi\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class JsonApiConfiguration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('jsonapi');
        $rootNode = $builder->getRootNode();
        $rootNode
            ->children()
                ->scalarNode('cache_key')
                    ->defaultNull()
                ->end()
                ->arrayNode('files')
                    ->defaultValue([])
                    ->prototype('scalar')
                    ->end()
                ->end()
            ->end();
        return $builder;
    }
}
