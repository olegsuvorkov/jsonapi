<?php

namespace JsonApi\Repository;

/**
 * @package JsonApi\Repository
 */
interface FilterRepositoryInterface
{
    /**
     * @param $criteria
     * @return mixed
     */
    public function findByCriteria($criteria);

    /**
     * @param $criteria
     * @return mixed
     */
    public function getCountByCriteria($criteria);
}
