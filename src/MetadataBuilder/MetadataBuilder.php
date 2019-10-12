<?php

namespace JsonApi\MetadataBuilder;

use JsonApi\Exception\LoaderException;
use JsonApi\Metadata\FieldInterface;
use JsonApi\Metadata\Metadata;
use ReflectionClass;
use ReflectionException;

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
     * @var MetadataBuilder[]
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
     * @var Metadata
     */
    private $metadata;

    /**
     * @var ReflectionClass
     */
    public $reflectionClass;

    /**
     * @param string $class
     * @throws LoaderException
     */
    public function __construct(string $class)
    {
        $this->class = $class;
        try {
            $this->reflectionClass = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new LoaderException('', 0, $e);
        }
    }

    /**
     * @param array $map
     * @return Metadata
     */
    public function getMetadata(array $map)
    {
        if ($this->metadata === null) {
            $this->metadata = new Metadata($this->class, $this->type);
            $inherits = $this->inherits;
            $inherits[] = $this;
            $data = array_fill_keys(['identifiers', 'attributes', 'relationships'], []);
            $class = $this->class;
            foreach ($this->discrimination as $discrimination) {
                $this->metadata->addDiscriminator($discrimination->getMetadata($map));
            }
            $transformer = function (FieldBuilder $builder) use ($map, $class) {
                try {
                    return $builder->getField($map);
                } catch (LoaderException $e) {
                    throw new LoaderException($e->getMessage().' in class '.$class, 0, $e);
                }
            };
            foreach ($data as $property => &$list) {
                $list = array_map($transformer, array_merge(...array_column($inherits, $property)));
                unset($list);
            }
            $this->sortField($data['attributes']);
            $this->sortField($data['relationships']);
            $this->metadata->setIdentifiers(array_values($data['identifiers']));
            $this->metadata->setAttributes(array_values($data['attributes']));
            $this->metadata->setRelationships(array_values($data['relationships']));
        }
        return $this->metadata;
    }

    private function sortField(array &$fields)
    {
        usort($fields, function(FieldInterface $left, FieldInterface $right) {
            return $left->getSerializeName() <=> $right->getSerializeName();
        });
    }

    /**
     * @param string|string[] $method
     * @return string
     * @throws LoaderException
     */
    public function getMethod($method)
    {
        $methods = (array) $method;
        foreach ($methods as $method) {
            if ($this->reflectionClass->hasMethod($method)) {
                return $method;
            }
        }
        throw LoaderException::invalidMethods($this->class, $methods);
    }
}
