<?php

namespace JsonApi\Exception;

use Throwable;

/**
 * @package JsonApi\Exception
 */
class InvalidTypeException extends Exception
{
    public static function undefinedType(string $type, Throwable $previous = null)
    {
        return new self(sprintf('Undefined type `%s`', $type), 0, $previous);
    }

    public static function invalidType()
    {
        return new self('Invalid type');
    }
}
