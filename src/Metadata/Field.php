<?php

namespace JsonApi\Metadata;

use JsonApi\Transformer\InvalidArgumentException;
use JsonApi\Transformer\TransformerInterface;
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
     * @var TransformerInterface
     */
    private $transformer;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @param string $name
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
     * @return string|null
     */
    public function getSetter(): ?string
    {
        return $this->setter;
    }

    public function getValue($object)
    {
        return $object->{$this->getter}();
    }

    /**
     * @param $object
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getNormalizeValue($object)
    {
        $value = $this->getValue($object);
        if ($value !== null) {
            $value = $this->transformer->transform($value, $this->options);
        }
        return $value;
    }

    /**
     * @param $object
     * @return mixed|string
     * @throws InvalidArgumentException
     */
    public function getScalarValue($object)
    {
        $value = $this->getValue($object);
        if ($value !== null) {
            $value = $this->transformer->transformScalar($value, $this->options);
        }
        return $value;
    }
//
//    /**
//     * @return string
//     */
//    public function getType(): string
//    {
//        return $this->type;
//    }

    /**
     * @param TransformerInterface $transformer
     */
    public function setTransformer(TransformerInterface $transformer): void
    {
        $this->transformer = $transformer;
        $this->type = $transformer->getType();
    }

    /**
     * @param string $name
     * @param mixed  $default
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
        return [
            'name',
            'serializeName',
            'context',
            'getter',
            'setter',
            'type',
            'options'
        ];
    }

    /**
     * @inheritDoc
     */
    public function __wakeup()
    {
        $this->transformer = TransformerPool::get($this->type);
    }
}
