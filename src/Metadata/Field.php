<?php

namespace JsonApi\Metadata;

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
     * @var string|null
     */
    private $role;

    /**
     * @var int
     */
    private $include = self::INCLUDE_DEFAULT;

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
     */
    public function __construct(string $name)
    {
        $this->name          = $name;
        $this->serializeName = $name;
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
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @param string|null $role
     * @return void
     */
    public function setRole(?string $role): void
    {
        $this->role = $role;
    }

    /**
     * @return int
     */
    public function getInclude(): int
    {
        return $this->include;
    }

    /**
     * @param int $include
     * @return void
     */
    public function setInclude(int $include): void
    {
        $this->include = $include;
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

    /**
     * @inheritDoc
     */
    public function __sleep()
    {
        // TODO: Implement __sleep() method.
    }
}
