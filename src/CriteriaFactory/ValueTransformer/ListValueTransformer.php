<?php


namespace JsonApi\CriteriaFactory\ValueTransformer;


class ListValueTransformer implements ValueTransformerInterface
{
    /**
     * @var ValueTransformerInterface
     */
    private $transformer;

    public function __construct(ValueTransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    public function transform(string $value)
    {
        $list = explode(',', $value);
        $result = [];
        foreach ($list as $item) {
            $result[] = $this->transformer->transform($item);
        }
        return $result;
    }
}
