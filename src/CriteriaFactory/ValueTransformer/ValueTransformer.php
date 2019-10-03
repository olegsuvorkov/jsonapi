<?php


namespace JsonApi\CriteriaFactory\ValueTransformer;


use JsonApi\Exception\ParseUrlException;

class ValueTransformer implements ValueTransformerInterface
{
    public function transform(string $value)
    {
        if ('"' === substr($value, 0, 1) &&
            '"' === substr($value, -1) &&
            strlen($value) >= 2
        ) {
            $value = substr($value, 1, strlen($value) - 2);
            $value = urldecode($value);
        } elseif ($value === 'true') {
            return true;
        } elseif ($value === 'false') {
            return false;
        } elseif ($value === 'null') {
            return null;
        } elseif (is_numeric($value)) {
            if (false !== strpos($value, '.')) {
                return floatval($value);
            } else {
                return intval($value);
            }
        } else {
            throw new ParseUrlException(sprintf('Invalid value `%s`', $value));
        }
        return $value;
    }
}
