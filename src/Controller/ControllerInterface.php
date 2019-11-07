<?php

namespace JsonApi\Controller;

use JsonApi\Context\ContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package JsonApi\Controller
 */
interface ControllerInterface
{
    /**
     * @return string
     */
    public function getType(): string;
}
