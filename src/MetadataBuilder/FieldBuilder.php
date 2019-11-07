<?php

namespace JsonApi\MetadataBuilder;

use JsonApi\Metadata\Field;
use JsonApi\MetadataBuilder\Configurator\ConfiguratorInterface;

/**
 * @package JsonApi\MetadataBuilder
 */
class FieldBuilder
{
    /**
     * @var bool
     */
    public $context = false;

    /**
     * @var string|null
     */
    public $serializeName = null;

    /**
     * @var bool
     */
    public $read = true;

    /**
     * @var string|null
     */
    public $getter = null;

    /**
     * @var bool
     */
    public $write = true;

    /**
     * @var string|null
     */
    public $setter = null;

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var string
     */
    private $name;

    /**
     * @var MetadataBuilder
     */
    private $metadataBuilder;

    /**
     * @var ConfiguratorInterface
     */
    private $configurator;

    /**
     * @var Field
     */
    private $field;

    /**
     * @param string $name
     * @param ConfiguratorInterface $configurator
     * @param MetadataBuilder $metadataBuilder
     */
    public function __construct(
        string $name,
        ConfiguratorInterface $configurator,
        MetadataBuilder $metadataBuilder
    ) {
        $this->name            = $name;
        $this->configurator    = $configurator;
        $this->metadataBuilder = $metadataBuilder;
    }

    /**
     * @param array $map
     * @return Field
     * @throws BuilderException
     */
    public function getField(array $map)
    {
        if ($this->field === null) {
            $getter = $this->read ? $this->getMethods($this->getter, ['get', 'is', 'has', 'getIs']) : null;
            $setter = $this->write ? $this->getMethods($this->setter, ['set', 'setIs', 'setHas']) : null;
            $this->field = new Field($this->name, $this->context, $getter, $setter);
            if ($this->serializeName) {
                $this->field->setSerializeName($this->serializeName);
            }
            try {
                $this->configurator->configure($this->field, $this->options, $map);
            } catch (BuilderException $e) {
                throw new BuilderException($e->getMessage().' in field '.$this->name, 0, $e);
            }
        }
        return $this->field;
    }

    /**
     * @param string|null $method
     * @param array $prefixes
     * @return string
     * @throws BuilderException
     */
    private function getMethods(?string $method, array $prefixes): string
    {
        if ($method === null) {
            $suffix  = ucfirst($this->name);
            $method = [];
            foreach ($prefixes as $prefix) {
                $method[] = $prefix.$suffix;
            }
        }
        $methods = (array) $method;
        $reflectionClass = $this->metadataBuilder->reflectionClass;
        foreach ($methods as $method) {
            if ($reflectionClass->hasMethod($method)) {
                return $method;
            }
        }
        throw BuilderException::invalidMethods($reflectionClass->getName(), $methods);
    }
}
