<?php

namespace JsonApi\Metadata;

use JsonApi\Transformer\InvalidArgumentException;
use JsonApi\Transformer\TransformerPool;

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
     * @param TransformerPool $pool
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getNormalizeValue($object, TransformerPool $pool);

    /**
     * @param $object
     * @param TransformerPool $pool
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getScalarValue($object, TransformerPool $pool);

    public function getType(): string;

    public function getOption(string $name, $default = null);

    public function setOption(string $name, $value);

    public function inContext(?array &$fields): bool;
}
