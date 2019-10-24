<?php

namespace JsonApi\Metadata;

/**
 * @package JsonApi\Metadata
 */
interface MetadataInjectionInterface
{
    /**
     * @param MetadataContainerInterface $metadataContainer
     * @return void
     */
    public function injectMetadataContainer(MetadataContainerInterface $metadataContainer): void;
}
