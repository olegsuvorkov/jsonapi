<?php

namespace JsonApi\Metadata;

use JsonApi\Loader\LoaderInterface;

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
     * @var MetadataContainerInterface
     */
    private $metadataContainer;

    /**
     * @param LoaderInterface $loader
     * @param MetadataContainerInterface $metadataContainer
     */
    public function __construct(LoaderInterface $loader, MetadataContainerInterface $metadataContainer)
    {
        $this->loader = $loader;
        $this->metadataContainer = $metadataContainer;
    }

    /**
     * @inheritDoc
     */
    public function createRegister(): RegisterInterface
    {
        $list = [];
        foreach ($this->loader->load() as $type => $item) {
            $item->initialize($this->metadataContainer);
            $list[$type] = $item;
        }
        return new Register($list);
    }
}
