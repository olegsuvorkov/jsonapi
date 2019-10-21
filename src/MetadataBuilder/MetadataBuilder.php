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
     * @var FieldBuilder[]|null
     */
    public $constructorArguments = null;

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
     * @var string
     */
    public $securityStrategy = null;

    /**
     * @var array
     */
    public $securityOptions = [];

    /**
     * @var string
     */
    public $securityNormalizeStrategy = null;

    /**
     * @var array
     */
    public $securityNormalizeOptions = [];

    /**
     * @var MetadataBuilder|null
     */
    public $parent = null;

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
     * @param MetadataBuilder[] $map
     * @return Metadata
     * @throws BuilderException
     */
    public function getMetadata(array $map)
    {
        if ($this->metadata === null) {
            $securityStrategy = $this->securityStrategy;
            $securityOptions = $this->securityOptions;
            $securityNormalizeStrategy = $this->securityNormalizeStrategy;
            $securityNormalizeOptions = $this->securityNormalizeOptions;
            if ($this->parent) {
                if ($securityStrategy === null) {
                    $securityStrategy = $this->parent->securityStrategy;
                    $securityOptions = $this->parent->securityOptions;
                }
                if ($securityNormalizeStrategy === null) {
                    $securityNormalizeStrategy = $this->parent->securityNormalizeStrategy;
                    $securityNormalizeOptions = $this->parent->securityNormalizeOptions;
                }
            }
            $this->metadata = new Metadata(
                $this->class,
                $this->type,
                $securityStrategy ?? 'none',
                $securityOptions,
                $securityNormalizeStrategy ?? 'none',
                $securityNormalizeOptions
            );
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
            $constructorArguments = [];
            foreach ($this->getInherits() as $inherit) {
                if ($inherit->constructorArguments !== null) {
                    $constructorArguments = $inherit->constructorArguments;
                }
                $identifiers = array_merge($identifiers, $inherit->identifiers);
                $attributes = array_merge($attributes, $inherit->attributes);
                $relationships = array_merge($relationships, $inherit->relationships);
            }
            $constructorArguments = $this->getFields($constructorArguments, $map);
            $this->metadata->setConstructorArguments($constructorArguments);
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

    /**
     * @return MetadataBuilder[]
     */
    private function getInherits()
    {
        return array_merge($this->inherits, [$this]);
    }
}
