<?php

namespace JsonApi\CriteriaFactory;

use Doctrine\Common\Collections\Criteria;
use JsonApi\Context\ContextInterface;
use JsonApi\CriteriaFactory\PrefixParser\PrefixParser;
use JsonApi\Exception\ParseUrlException;
use JsonApi\Pagination\PaginationFactory;
use JsonApi\Pagination\PaginationFactoryInterface;
use JsonApi\Sorting\SortingFactory;
use JsonApi\Sorting\SortingFactoryInterface;

/**
 * @package JsonApi\Criteria
 */
class PrefixCriteriaFactory implements CriteriaFactoryInterface
{
    /**
     * @var SortingFactoryInterface
     */
    private $sortingFactory;

    /**
     * @var PaginationFactoryInterface
     */
    private $paginationFactory;

    public function __construct()
    {
        $this->sortingFactory = new SortingFactory();
        $this->paginationFactory = new PaginationFactory();
    }

    /**
     * @inheritDoc
     */
    public function createCriteria(ContextInterface $context, $filter, $sorting, $page): Criteria
    {
        if (is_string($filter)) {
            $index = 0;
            $expression = (new PrefixParser())->parse($filter, $index, $context);
            [$firstResult, $maxResults] = $this->paginationFactory->createPagination($page);
            return Criteria::create()
                ->where($expression)
                ->orderBy($this->sortingFactory->createSorting($context, $sorting))
                ->setFirstResult($firstResult)
                ->setMaxResults($maxResults);
        }
        throw new ParseUrlException();
    }
}
