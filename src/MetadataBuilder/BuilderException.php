<?php

namespace JsonApi\MetadataBuilder;

use Throwable;

/**
 * @package JsonApi\MetadataBuilder
 */
class BuilderException extends \Exception
{
    public static function invalidField(string $name, Throwable $e)
    {
        return new BuilderException(sprintf('Invalid field `%s`', $name), 0, $e);
    }

    public static function invalidPropertyExpected(string $property, string $type)
    {
        return new self(sprintf('Invalid property `%s` expected %s', $property, $type));
    }

    /**
     * @param string $property
     * @param Throwable $e
     * @return BuilderException
     */
    public static function invalidFieldProperty(string $property, Throwable $e = null)
    {
        return new self(sprintf('Invalid property `%s`.', $property), 2, $e);
    }

    /**
     * @param string $class
     * @param array $methods
     * @return BuilderException
     */
    public static function invalidMethods(string $class, array $methods)
    {
        return new self('Undefined method `'.implode('`, `', $methods).'` in class `'.$class.'`');
    }
}