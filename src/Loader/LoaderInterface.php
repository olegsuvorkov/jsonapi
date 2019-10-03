<?php

namespace JsonApi\Loader;

use JsonApi\Exception\LoaderException;
use JsonApi\Metadata\RegisterInterface;

/**
 * @package JsonApi\Loader
 */
interface LoaderInterface
{
    /**
     * @param RegisterInterface $register
     * @throws LoaderException
     */
    public function load(RegisterInterface $register): void;
}
