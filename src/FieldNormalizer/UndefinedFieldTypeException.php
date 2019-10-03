<?php


namespace JsonApi\FieldNormalizer;


class UndefinedFieldTypeException extends \Exception
{
    public static function invalidType(string $type)
    {
        return new self(sprintf('Undefined type `%s`', $type));
    }
}
