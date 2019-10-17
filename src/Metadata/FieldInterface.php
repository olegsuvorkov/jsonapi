<?php

namespace JsonApi\Metadata;

use JsonApi\Transformer\InvalidArgumentException;

/**
 * @package JsonApi\Metadata\Field
 */
interface FieldInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getSerializeName(): string;

    /**
     * @param $object
     * @return mixed
     */
    public function getValue($object);

    /**
     * @param $object
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getNormalizeValue($object);

    /**
     * @param $object
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getScalarValue($object);

    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function getOption(string $name, $default = null);

    /**
     * @param array|null $fields
     * @return bool
     */
    public function inContext(?array &$fields): bool;
}
