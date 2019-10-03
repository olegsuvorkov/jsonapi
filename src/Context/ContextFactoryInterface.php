<?php

namespace JsonApi\Context;

use JsonApi\Exception\ParseUrlException;
use JsonApi\Exception\InvalidTypeException;

/**
 * @package JsonApi
 */
interface ContextFactoryInterface
{
    /**
     * @param string $type
     * @param string $include
     * @param string[][] $fields
     * @return ContextInterface
     *@throws InvalidTypeException
     * @throws ParseUrlException
     */
    public function createContext($type, $include, $fields): ContextInterface;
}
