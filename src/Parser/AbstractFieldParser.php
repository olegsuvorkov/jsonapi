<?php

namespace JsonApi\Parser;

use JsonApi\Exception\LoaderException;
use JsonApi\Metadata\Field;
use JsonApi\Metadata\FieldInterface;

/**
 * @package JsonApi\Parser
 */
abstract class AbstractFieldParser implements FieldParserInterface
{
    /**
     * @inheritDoc
     */
    public function parseField(Field $field, &$parameters): void
    {
        $include = $parameters['include'] ?? 'default';
        unset($parameters['include']);
        if ($include === 'default') {
            $field->setInclude(FieldInterface::INCLUDE_DEFAULT);
        } elseif ($include === 'context') {
            $field->setInclude(FieldInterface::INCLUDE_CONTEXT);
        } else {
            throw new LoaderException();
        }
        $role = $parameters['role'] ?? $this;
        unset($parameters['role']);
        if ($role !== $this) {
            if (is_string($role)) {
                $field->setRole($role);
            } else {
                throw new LoaderException();
            }
        }
    }
}
