<?php

namespace JsonApi\Metadata;

use JsonApi\Loader\LoaderInterface;
use JsonApi\SecurityStrategy\SecurityStrategyBuilderPool;
use JsonApi\Transformer\TransformerPool;

/**
 * @package JsonApi\Metadata
 */
class RegisterFactory implements RegisterFactoryInterface
{
    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var SecurityStrategyBuilderPool
     */
    private $securityPool;

    /**
     * @param LoaderInterface $loader
     * @param SecurityStrategyBuilderPool $securityPool
     * @param array $transformers
     */
    public function __construct(
        LoaderInterface $loader,
        SecurityStrategyBuilderPool $securityPool,
        array $transformers
    ) {
        $this->loader = $loader;
        $this->securityPool = $securityPool;
        foreach ($transformers as $transformer) {
            TransformerPool::add($transformer);
        }
    }

    /**
     * @inheritDoc
     */
    public function createRegister(): RegisterInterface
    {
        $list = [];
        foreach ($this->loader->load() as $type => $item) {
            $item->initializeSecurity($this->securityPool);
            $list[$type] = $item;
        }
        return new Register($list);
    }
}
