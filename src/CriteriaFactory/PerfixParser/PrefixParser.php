<?php


namespace JsonApi\CriteriaFactory\PrefixParser;

use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use JsonApi\Context\ContextInterface;
use JsonApi\CriteriaFactory\ValueTransformer\ListValueTransformer;
use JsonApi\CriteriaFactory\ValueTransformer\ValueTransformer;
use JsonApi\Exception\ParseUrlException;

/**
 * @package JsonApi\CriteriaFactory\PrefixParser
 */
class PrefixParser extends AbstractPrefixParser
{
    /**
     * @var PrefixParserInterface[]
     */
    private $map = [];

    public function __construct()
    {
        $transformer = new ValueTransformer();
        $listTransformer = new ListValueTransformer($transformer);
        $this->map = [
            'eq'            => new ComparisonPrefixParser(Comparison::EQ, $transformer),
            'neq'           => new ComparisonPrefixParser(Comparison::NEQ, $transformer),
            'lt'            => new ComparisonPrefixParser(Comparison::LT, $transformer),
            'lte'           => new ComparisonPrefixParser(Comparison::LTE, $transformer),
            'gt'            => new ComparisonPrefixParser(Comparison::GT, $transformer),
            'gte'           => new ComparisonPrefixParser(Comparison::GTE, $transformer),
            'in'            => new ComparisonPrefixParser(Comparison::IN, $listTransformer),
            'nin'           => new ComparisonPrefixParser(Comparison::NIN, $listTransformer),
            'contains'      => new ComparisonPrefixParser(Comparison::CONTAINS, $transformer),
            'starts_with'   => new ComparisonPrefixParser(Comparison::STARTS_WITH, $transformer),
            'ends_with'     => new ComparisonPrefixParser(Comparison::ENDS_WITH, $transformer),
            'and'           => new CompositePrefixParser(CompositeExpression::TYPE_AND, $this),
            'or'            => new CompositePrefixParser(CompositeExpression::TYPE_OR, $this),
        ];
    }

    /**
     * @inheritDoc
     */
    public function parse(string $data, int &$index, ContextInterface $context): Expression
    {
        $prefix = $this->match($data, ':', $index);
        if (isset($this->map[$prefix])) {
            return $this->map[$prefix]->parse($data, $index, $context);
        }
        throw new ParseUrlException();
    }
}
