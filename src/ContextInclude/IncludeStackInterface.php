<?php

namespace JsonApi\ContextInclude;

/**
 * @package JsonApi\ContextInclude
 */
interface IncludeStackInterface
{
    public function add($object): void;

    public function all();
}