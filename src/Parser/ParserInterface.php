<?php

namespace JsonApi\Parser;

use JsonApi\Exception\LoaderException;

/**
 * @package JsonApi\Parser
 */
interface ParserInterface
{
    /**
     * @param array $data
     * @throws LoaderException
     */
    public function load(array &$data): void;
}
