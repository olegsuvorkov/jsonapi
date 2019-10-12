<?php

namespace JsonApi\Context;

use JsonApi\Metadata\MetadataInterface;

/**
 * @package JsonApi
 */
interface ContextInterface
{
    /**
     * @return MetadataInterface
     */
    public function getMetadata(): MetadataInterface;
}
