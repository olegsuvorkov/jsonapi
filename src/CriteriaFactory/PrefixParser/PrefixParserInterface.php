<?php

namespace JsonApi\CriteriaFactory\PrefixParser;

use Doctrine\Common\Collections\Expr\Expression;
use JsonApi\Context\ContextInterface;
use JsonApi\Exception\ParseUrlException;

/**
 * @package JsonApi\CriteriaFactory\PrefixParser
 */
interface PrefixParserInterface
{
    /**
     * @param string $data
     * @param int $index
     * @param ContextInterface $context
     * @return Expression
     * @throws ParseUrlException
     */
    public function parse(string $data, int &$index, ContextInterface $context): Expression;
}
