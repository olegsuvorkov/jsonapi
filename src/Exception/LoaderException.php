<?php

namespace JsonApi\Exception;

use Throwable;

class LoaderException extends Exception
{
    public static function invalidMethods(string $class, array $methods)
    {
        return new LoaderException('Undefined method `'.implode('`, `', $methods).'` in class `'.$class.'`');
    }

    /**
     * @param string $field
     * @param string $property
     * @param string $type
     * @return LoaderException
     */
    public static function expectedFieldProperty(string $field, string $property, string $type)
    {
        $message = sprintf('Field `%s` invalid property `%s` expected `%s`', $field, $property, $type);
        return new LoaderException($message);
    }

    public static function wrapInClass(Throwable $e, string $class)
    {
        return new LoaderException($e->getMessage().' in class '.$class, 0, $e);
    }
}