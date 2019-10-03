<?php

namespace JsonApi\Pagination;

/**
 * @package JsonApi
 */
interface PaginationFactoryInterface
{
    public function createPagination($page): array;
}
