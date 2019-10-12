<?php

namespace JsonApi\Metadata;

use JsonApi\Transformer\InvalidArgumentException;
use JsonApi\Transformer\TransformerPool;

/**
 * @package JsonApi\Metadata\Field
 */
class Field implements FieldInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $serializeName;

    /**
     * @var bool
     */
    private $context = false;

    /**
     * @var string|null
     */
    private $getter;

    /**
     * @var string|null
     */
    private $setter;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @param string $name
     * @param string $type
     * @param bool $context
     * @param string|null $getter
     * @param string|null $setter
     */
    public function __construct(string $name, bool $context, ?string $getter, ?string $setter)
    {
        $this->name          = $name;
        $this->serializeName = $name;
        $this->context       = $context;
        $this->getter        = $getter;
        $this->setter        = $setter;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSerializeName(): string
    {
        return $this->serializeName;
    }

    /**
     * @param string $serializeName
     * @return void
     */
    public function setSerializeName(string $serializeName): void
    {
        $this->serializeName = $serializeName;
    }

    /**
     * @return string|null
     */
    public function getGetter(): ?string
    {
        return $this->getter;
    }

    /**
     * @param string $getter
     */
    public function setGetter(?string $getter): void
    {
        $this->getter = $getter;
    }

    /**
     * @return string|null
     */
    public function getSetter(): ?string
    {
        return $this->setter;
    }

    /**
     * @param string|null $setter
     */
    public function setSetter(?string $setter): void
    {
        $this->setter = $setter;
    }

    public function getValue($object)
    {
        return $object->{$this->getter}();
    }

    /**
     * @param $object
     * @param TransformerPool $pool
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getNormalizeValue($object, TransformerPool $pool)
    {
        $value = $this->getValue($object);
        if ($value !== null) {
            return $pool->get($this->type)->transform($value, $this->options);
        }
        return $value;
    }

    /**
     * @param $object
     * @param TransformerPool $pool
     * @return mixed|string
     * @throws InvalidArgumentException
     */
    public function getScalarValue($object, TransformerPool $pool)
    {
        $value = $this->getValue($object);
        $metadata = $this->options['target'] ?? null;
        if ($value !== null) {
            if ($metadata instanceof MetadataInterface) {
                $value = $metadata->getId($value, $pool);
            } else {
                $value = $this->getNormalizeValue($object, $pool);
            }
        }
        return $value;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getOption(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function setOption(string $name, $value)
    {
        return $this->options[$name] = $value;
    }

    public function inContext(?array &$fields): bool
    {
        if ($this->getter === null) {
            return false;
        } elseif ($fields === null) {
            return !$this->context;
        } elseif (false !== ($key = array_search($this->serializeName, $fields))) {
            unset($fields[$key]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function __sleep()
    {
    }
}
