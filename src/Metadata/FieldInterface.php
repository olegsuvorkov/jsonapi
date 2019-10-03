<?php

namespace JsonApi\Metadata;

/**
 * @package JsonApi\Metadata\Field
 */
interface FieldInterface
{
    const INCLUDE_DEFAULT   = 0;
    const INCLUDE_CONTEXT   = 1;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getSerializeName(): string;

    /**
     * @return string|null
     */
    public function getRole(): ?string;

    /**
     * @return int
     */
    public function getInclude(): int;

    /**
     * @param $object
     * @return mixed
     */
    public function getValue($object);

    public function getType(): string;

    public function setType(string $type): void;

    public function getOption(string $name, $default = null);

    public function setOption(string $name, $value);
}
