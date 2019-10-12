<?php

namespace JsonApi\Loader;

use JsonApi\Exception\LoaderException;
use JsonApi\Metadata\MetadataInterface;

/**
 * @package JsonApi\Loader
 */
interface LoaderInterface
{
    /**
     * @return MetadataInterface[]
     * @throws LoaderException
     */
    public function load(): array;
}
