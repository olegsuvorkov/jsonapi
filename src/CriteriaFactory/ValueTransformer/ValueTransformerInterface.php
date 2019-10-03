<?php


namespace JsonApi\CriteriaFactory\ValueTransformer;


interface ValueTransformerInterface
{
    public function transform(string $value);
}