<?php

namespace JsonApi\Parser;

use JsonApi\Metadata\MetadataInterface;

/**
 * @package JsonApi\Parser
 */
interface MetadataParserInterface
{
    /**
     * @param MetadataInterface $metadata
     * @param $parameters
     * @return void
     */
    public function parseMetadata(MetadataInterface $metadata, array $parameters): void;
}
