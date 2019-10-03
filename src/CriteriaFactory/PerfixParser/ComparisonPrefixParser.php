<?php

namespace JsonApi\CriteriaFactory\PrefixParser;

use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\Expression;
use JsonApi\Context\ContextInterface;
use JsonApi\CriteriaFactory\ValueTransformer\ValueTransformerInterface;

/**
 * @package JsonApi\CriteriaFactory\PrefixParser
 */
class ComparisonPrefixParser extends AbstractPrefixParser
{
    /**
     * @var string
     */
    private $operator;
    /**
     * @var ValueTransformerInterface
     */
    private $transformer;

    /**
     * @param string $operator
     * @param ValueTransformerInterface $transformer
     */
    public function __construct(string $operator, ValueTransformerInterface $transformer)
    {
        $this->operator = $operator;
        $this->transformer = $transformer;
    }

    /**
     * @inheritDoc
     */
    public function parse(string $data, int &$index, ContextInterface $context): Expression
    {
        $field = $this->match($data, ':', $index);
        $field = $context->getField(explode('.', $field));
        $value = $this->match($data, ';', $index);
        return new Comparison($field, $this->operator, $this->transformer->transform($value));
    }
}
