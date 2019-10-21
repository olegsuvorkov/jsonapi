<?php

namespace JsonApi\Metadata;

use JsonApi\Exception\LoaderException;

/**
 * @package JsonApi\Metadata
 */
interface RegisterFactoryInterface
{
    /**
     * @return RegisterInterface
     * @throws LoaderException
     */
    public function createRegister(): RegisterInterface;
}
