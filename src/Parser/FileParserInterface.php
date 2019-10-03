<?php

namespace JsonApi\Parser;

use JsonApi\Exception\LoaderException;
use JsonApi\Metadata\UndefinedMetadataException;

/**
 * @package JsonApi\Parser
 */
interface FileParserInterface
{
    /**
     * @throws LoaderException
     */
    public function load(): void;

    /**
     * @throws LoaderException
     * @throws UndefinedMetadataException
     */
    public function normalize(): void;

    /**
     * @throws UndefinedMetadataException
     */
    public function parse(): void;
}
