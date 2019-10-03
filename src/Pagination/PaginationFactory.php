<?php

namespace JsonApi\Pagination;

use JsonApi\Exception\ParseUrlException;

/**
 * @package JsonApi\Pagination
 */
class PaginationFactory implements PaginationFactoryInterface
{
    /**
     * @param $page
     * @return array
     * @throws ParseUrlException
     */
    public function createPagination($page): array
    {
        if (!$page) {
            $page = [];
        }
        if (is_array($page)) {
            $offset = null;
            $limit  = null;
            foreach ($page as $key => $value) {
                if ($key === 'offset') {
                    $offset = $this->parseInt($value);
                } elseif ($key === 'limit') {
                    $limit = $this->parseInt($value);
                } else {
                    throw new ParseUrlException();
                }
            }
            if ($limit !== null && $offset === null) {
                $offset = 0;
            }
            return [$offset, $limit];
        }
        throw new ParseUrlException();
    }

    /**
     * @param $value
     * @return int
     * @throws ParseUrlException
     */
    private function parseInt($value): int
    {
        if (ctype_digit($value)) {
            return (int) $value;
        }
        throw new ParseUrlException();
    }
}
