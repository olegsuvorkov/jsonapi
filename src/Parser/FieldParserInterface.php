<?php

namespace JsonApi\Parser;

use JsonApi\Exception\LoaderException;
use JsonApi\Metadata\Field;

/**
 * @package JsonApi\Parser
 */
interface FieldParserInterface
{
    /**
     * @param Field $field
     * @param $parameters
     * @throws LoaderException
     */
    public function parseField(Field $field, &$parameters): void;
}
