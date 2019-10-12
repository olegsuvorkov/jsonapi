<?php

namespace JsonApi\Sorting;

use Doctrine\Common\Collections\Criteria;
use JsonApi\Context\ContextInterface;
use JsonApi\Exception\ParseUrlException;

/**
 * @package JsonApi
 */
class SortingFactory implements SortingFactoryInterface
{
    /**
     * @param ContextInterface $context
     * @param $sorting
     * @return array
     * @throws ParseUrlException
     */
    public function createSorting(ContextInterface $context, $sorting): array
    {
        if (!$sorting) {
            $sorting = '';
        }
        if (is_string($sorting)) {
            $list = [];
//            foreach (explode(',', $sorting) as $sort) {
//                $direct = Criteria::ASC;
//                if (0 === strncmp('-', $sort, 1)) {
//                    $direct = Criteria::DESC;
//                    $sort = substr($sort, 1);
//                }
//                $field = $context->getField(explode('.', $sort));
//                $list[$field] = $direct;
//            }
            return $list;
        }
        throw new ParseUrlException();
    }
}
