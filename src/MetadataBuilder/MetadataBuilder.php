<?php

namespace JsonApi\MetadataBuilder;

use JsonApi\Metadata\FieldInterface;
use JsonApi\Metadata\Metadata;
use ReflectionClass;
use ReflectionException;

/**
 * @package JsonApi\MetadataBuilder
 */
class MetadataBuilder
{
    /**
     * @var FieldBuilder[]
     */
    public $identifiers = [];

    /**
     * @var FieldBuilder[]
     */
    public $attributes = [];

    /**
     * @var FieldBuilder[]
     */
    public $relationships = [];

    /**
     * @var array
     */
    public $metas = [];

    /**
     * @var string[]
     */
    public $discrimination = [];

    /**
     * @var MetadataBuilder[]
     */
    public $inherits = [];

    /**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    public $type;

    /**
     * @var ReflectionClass
     */
    public $reflectionClass;

    /**
     * @var Metadata
     */
    private $metadata;

    /**
     * @var callable
     */
    private $sortCallback;

    /**
     * @param string $class
     * @param callable $sortCallback
     * @throws BuilderException
     */
    public function __construct(string $class, callable $sortCallback)
    {
        $this->class = $class;
        $this->sortCallback = $sortCallback;
        try {
            $this->reflectionClass = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new BuilderException(sprintf('Not find class `%s`', $class), 0, $e);
        }
    }

    /**
     * @param array $map
     * @return Metadata
     * @throws BuilderException
     */
    public function getMetadata(array $map)
    {
        if ($this->metadata === null) {
            $this->metadata = new Metadata($this->class, $this->type);
            foreach ($this->discrimination as $discrimination) {
                if (isset($map[$discrimination])) {
                    $this->metadata->addDiscriminator($map[$discrimination]->getMetadata($map));
                } else {
                    throw new BuilderException();
                }
            }
            $identifiers   = [];
            $attributes    = [];
            $relationships = [];
            foreach ($this->getInherits() as $inherit) {
                $identifiers = array_merge($identifiers, $inherit->identifiers);
                $attributes = array_merge($attributes, $inherit->attributes);
                $relationships = array_merge($relationships, $inherit->relationships);
            }
            $identifiers = $this->getFields($identifiers, $map);
            $this->metadata->setIdentifiers($identifiers);
            $attributes = $this->getFields($attributes, $map);
            usort($attributes, $this->sortCallback);
            $this->metadata->setAttributes($attributes);
            $relationships = $this->getFields($relationships, $map);
            usort($relationships, $this->sortCallback);
            $this->metadata->setRelationships($relationships);
        }
        return $this->metadata;
    }

    /**
     * @param FieldBuilder[] $builders
     * @param array $map
     * @return FieldInterface[]
     * @throws BuilderException
     */
    private function getFields(array $builders, array $map): array
    {
        $fields = [];
        foreach ($builders as $builder) {
            $fields[] = $builder->getField($map);
        }
        return $fields;
    }

    private function getInherits()
    {
        return array_merge($this->inherits, [$this]);
    }
}
