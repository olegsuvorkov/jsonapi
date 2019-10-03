<?php

namespace JsonApi\CriteriaFactory\PrefixParser;

use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\Common\Collections\Expr\Expression;
use JsonApi\Context\ContextInterface;
use JsonApi\Exception\ParseUrlException;

/**
 * @package JsonApi\CriteriaFactory\PrefixParser
 */
class CompositePrefixParser implements PrefixParserInterface
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var PrefixParserInterface
     */
    private $parser;

    public function __construct(string $type, PrefixParserInterface $parser)
    {
        $this->type   = $type;
        $this->parser = $parser;
    }

    /**
     * @inheritDoc
     */
    public function parse(string $data, int &$index, ContextInterface $context): Expression
    {
        $list = [];
        $delimiter = null;
        do {
            $list[] = $this->parser->parse($data, $index, $context);
            $delimiter = $data[$index];
            $index++;
        } while (':' === $delimiter);
        if ($delimiter === ';') {
            return new CompositeExpression($this->type, $list);
        }
        throw new ParseUrlException();
    }
}
