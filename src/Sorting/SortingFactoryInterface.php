<?php

namespace JsonApi\Sorting;

use JsonApi\Context\ContextInterface;
use JsonApi\Exception\ParseUrlException;

/**
 * @package JsonApi\Sorting
 */
interface SortingFactoryInterface
{
    /**
     * @param ContextInterface $context
     * @param $sorting
     * @return array
     * @throws ParseUrlException
     */
    public function createSorting(ContextInterface $context, $sorting): array;
}
