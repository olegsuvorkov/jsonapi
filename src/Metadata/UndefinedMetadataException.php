<?php

namespace JsonApi\Metadata;

use JsonApi\Exception\Exception;

/**
 * @package JsonApi\Metadata
 */
class UndefinedMetadataException extends Exception
{
    public static function notFindByClass(string $class): UndefinedMetadataException
    {
        return new self(sprintf('Not find metadata by class `%s`', $class));
    }

    public static function notFindByType(string $type): UndefinedMetadataException
    {
        return new self(sprintf('Not find metadata by type `%s`', $type));
    }
}
