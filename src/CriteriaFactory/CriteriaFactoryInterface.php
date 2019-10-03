<?php

namespace JsonApi\CriteriaFactory;

use Doctrine\Common\Collections\Criteria;
use JsonApi\Context\ContextInterface;
use JsonApi\Exception\ParseUrlException;

/**
 * @package JsonApi
 */
interface CriteriaFactoryInterface
{
    /**
     * @param ContextInterface $context
     * @param $filter
     * @param $sorting
     * @param $page
     * @return Criteria
     * @throws ParseUrlException
     */
    public function createCriteria(ContextInterface $context, $filter, $sorting, $page): Criteria;
}
