<?php

namespace JsonApi\MetadataBuilder;

use JsonApi\Metadata\FieldInterface;

/**
 * @package JsonApi\MetadataBuilder
 */
class MetadataBuilderFactory
{
    /**
     * @var FieldBuilderFactory
     */
    private $attributes;

    /**
     * @var FieldBuilderFactory
     */
    private $relationships;

    /**
     * @var callable
     */
    private $sortCallback;

    /**
     * @param FieldBuilderFactory $attributes
     * @param FieldBuilderFactory $relationships
     */
    public function __construct(FieldBuilderFactory $attributes, FieldBuilderFactory $relationships)
    {
        $this->attributes = $attributes;
        $this->relationships = $relationships;
        $this->sortCallback = function (FieldInterface $left, FieldInterface $right) {
            return $left->getSerializeName() <=> $right->getSerializeName();
        };
    }

    /**
     * @param array $list
     * @return array
     * @throws BuilderException
     */
    public function createMetadataList(array $list)
    {
        $map = $this->createMetadataBuilderList($list);
        $result = [];
        foreach ($map as $type => $builder) {
            $result[$type] = $builder->getMetadata($map);
        }
        return array_reverse($result);
    }

    /**
     * @param array $list
     * @return MetadataBuilder[]
     * @throws BuilderException
     */
    private function createMetadataBuilderList(array $list)
    {
        uksort($list, function (string $left, string $right) {
            return is_a($left, $right, true) ? 1 : -1;
        });
        $map = [];
        $result = [];
        foreach ($list as $class => $parameters) {
            $file = $parameters['file'] ?? null;
            unset($parameters['file']);
            try {
                $builder = $this->createMetadataBuilder($class, $parameters);
                foreach ($map as $inheritClass => $inheritBuilder) {
                    if (is_a($class, $inheritClass, true)) {
                        $builder->inherits[] = $inheritBuilder;
                    }
                }
                if ($builder->type !== null) {
                    $result[$builder->type] = $builder;
                }
                $map[$class] = $builder;
            } catch (BuilderException $e) {
                throw new BuilderException($e->getMessage().' in `'.$class.'` ['.$file.']', 0, $e);
            }
        }
        return $result;
    }

    /**
     * @param string $class
     * @param array $parameters
     * @return MetadataBuilder
     * @throws BuilderException
     */
    private function createMetadataBuilder(string $class, array $parameters): MetadataBuilder
    {
        try {
            $builder = new MetadataBuilder($class, $this->sortCallback);
            $identifiers = $parameters['identifiers'] ?? [];
            unset($parameters['identifiers']);
            $parameters['identifiers'] = $identifiers;
            foreach ($parameters as $parameter => $value) {
                $this->buildProperty($builder, $parameter, $value);
            }
            return $builder;
        } catch (BuilderException $e) {
            throw new BuilderException($e->getMessage().' in class `'.$class.'`', 0, $e);
        }
    }

    /**
     * @param MetadataBuilder $builder
     * @param $parameter
     * @param $value
     * @throws BuilderException
     */
    private function buildProperty(MetadataBuilder $builder, $parameter, $value): void
    {
        if ($parameter === 'type') {
            if (!is_string($value)) {
                throw new BuilderException('Invalid property `type` expected string');
            }
            if (false === preg_match('~^[a-zA-Z0-9_]$~', $value)) {
                throw new BuilderException('Invalid property `type` invalid type');
            }
            $builder->type = $value;
        } elseif ($parameter === 'discrimination') {
            if (!is_array($value)) {
                throw new BuilderException('Invalid property `discrimination` expected array');
            }
            foreach ($value as $item) {
                if (!is_string($item)) {
                    throw new BuilderException('Invalid property `discrimination` expected string list');
                }
            }
            $builder->discrimination = array_values($value);
        } elseif ($parameter === 'attributes') {
            $builder->attributes = $this->attributes->createFieldBuilderList($builder, $value);
        } elseif ($parameter === 'relationships') {
            $builder->relationships = $this->relationships->createFieldBuilderList($builder, $value);
        } elseif ($parameter === 'identifiers') {
            $builder->identifiers = $this->attributes->createIdentifierBuilderList($builder, $value);
        } elseif ($parameter === 'meta') {
        } else {
            throw new BuilderException(sprintf('Undefined property `%s`', $parameter));
        }
    }
}
