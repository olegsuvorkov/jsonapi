<?php

namespace JsonApi\Context;

use JsonApi\ContextInclude\ContextIncludeInterface;
use JsonApi\Metadata\MetadataInterface;
use JsonApi\Metadata\RegisterInterface;

/**
 * @package JsonApi
 */
interface ContextInterface extends RegisterInterface
{
    /**
     * @return MetadataInterface
     */
    public function getMetadata(): MetadataInterface;

    /**
     * @return ContextIncludeInterface
     */
    public function getInclude(): ContextIncludeInterface;
}
