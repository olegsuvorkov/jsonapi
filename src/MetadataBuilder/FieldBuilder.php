<?php

namespace JsonApi\MetadataBuilder;

use JsonApi\Exception\LoaderException;
use JsonApi\Metadata\Field;
use JsonApi\TransformerConfigurator\TransformerConfiguratorInterface;

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
     * @var TransformerConfiguratorInterface
     */
    private $configurator;

    /**
     * @var Field
     */
    private $field;

    /**
     * @param string $name
     * @param TransformerConfiguratorInterface $configurator
     * @param MetadataBuilder $metadataBuilder
     */
    public function __construct(
        string $name,
        TransformerConfiguratorInterface $configurator,
        MetadataBuilder $metadataBuilder
    ) {
        $this->name            = $name;
        $this->configurator    = $configurator;
        $this->metadataBuilder = $metadataBuilder;
    }

    /**
     * @param array $map
     * @return Field
     * @throws LoaderException
     */
    public function getField(array $map)
    {
        if ($this->field === null) {
            $getter = $this->read ? $this->getMethods($this->getter, ['get', 'is', 'has', 'getIs']) : null;
            $setter = $this->write ? $this->getMethods($this->setter, ['set', 'setIs', 'setHas']) : null;
            $this->field = new Field($this->name, $this->context, $getter, $setter);
            try {
                $this->configurator->configure($this->field, $this, $map);
            } catch (LoaderException $e) {
                throw new LoaderException($e->getMessage().' in field '.$this->name, 0, $e);
            }
        }
        return $this->field;
    }

    /**
     * @param string|null $method
     * @param array $prefixes
     * @return string
     * @throws LoaderException
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
        return $this->metadataBuilder->getMethod($method);
    }
}
