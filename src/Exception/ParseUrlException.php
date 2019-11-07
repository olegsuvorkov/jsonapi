<?php

namespace JsonApi\Exception;

/**
 * @package JsonApi\Exception
 */
class ParseUrlException extends Exception
{
    public static function invalidQueryParameter(string $parameter)
    {
        return new self(sprintf('Include query parameter `%s`', $parameter));
    }

    public static function invalidQueryFieldType(string $parameter, string $type)
    {
        return new self(sprintf('Include query parameter `%s[%s]`', $parameter, $type));
    }

    public static function invalidTypeField(string $type, string $field)
    {
        return new self(sprintf('Invalid field `%s` in type `%s`', $field, $type));
    }
}
