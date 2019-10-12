<?php

namespace JsonApi\MetadataBuilder;

use JsonApi\Exception\LoaderException;

/**
 * @package JsonApi\MetadataBuilder
 */
class MetadataBuilderFactory
{
    /**
     * @var FieldBuilderFactory[]
     */
    private $factory;

    /**
     * @param FieldBuilderFactory $attributes
     * @param FieldBuilderFactory $relationships
     */
    public function __construct(FieldBuilderFactory $attributes, FieldBuilderFactory $relationships)
    {
        $this->factory['attributes'] = $attributes;
        $this->factory['relationships'] = $relationships;
    }

    /**
     * @param array $list
     * @return MetadataBuilder[]
     * @throws LoaderException
     */
    public function createMetadataBuilderList(array $list)
    {
        $this->sortInherit($list);
        $classes = array_keys($list);
        $inheritMap = $this->getInheritMap($classes);
        /** @var MetadataBuilder[][] $discriminationMap */
        $discriminationMap = array_fill_keys($classes, []);
        $map = [];
        $result = [];
        foreach ($list as $class => $parameters) {
            try {
                $discrimination = $parameters['discrimination'] ?? [];
                unset($parameters['discrimination']);
                if (!is_array($discrimination)) {
                    throw new LoaderException('Invalid property discrimination expected array');
                }
                $metadataBuilder = $this->createMetadataBuilder($class, $parameters);
                foreach ($inheritMap[$class] as $inheritClass) {
                    $metadataBuilder->inherits[] = $map[$inheritClass];
                }
                foreach ($discriminationMap[$class] as $originalMetadataBuilder) {
                    $originalMetadataBuilder->discrimination[] = $metadataBuilder;
                }
                foreach ($discrimination as $targetClass) {
                    if (is_string($targetClass)) {
                        if (isset($discriminationMap[$targetClass])) {
                            $discriminationMap[$targetClass][] = $metadataBuilder;
                        } else {
                            throw new LoaderException();
                        }
                    } else {
                        throw new LoaderException('Invalid discrimination item');
                    }
                }
                if ($metadataBuilder->type !== null) {
                    $result[$metadataBuilder->type] = $metadataBuilder;
                }
                $map[$class] = $metadataBuilder;
            } catch (LoaderException $e) {
                throw new LoaderException($e->getMessage().' in '.$class, 0, $e);
            }
        }
        return $result;
    }

    /**
     * @param string $class
     * @param array $parameters
     * @return MetadataBuilder
     * @throws LoaderException
     */
    private function createMetadataBuilder(string $class, array $parameters): MetadataBuilder
    {
        try {
            $builder = new MetadataBuilder($class);
            $identifiers = $parameters['identifiers'] ?? [];
            unset($parameters['identifiers']);
            unset($parameters['meta']);
            $fields = [];
            foreach ($parameters as $parameter => $value) {
                if ($parameter === 'type') {
                    if (!is_string($value)) {
                        throw new LoaderException('Invalid property `type` expected string');
                    }
                    if (false === preg_match('~^[a-zA-Z0-9_]$~', $value)) {
                        throw new LoaderException('Invalid property `type` invalid type');
                    }
                    $builder->type = $value;
                } elseif ($parameter === 'attributes' || $parameter === 'relationships') {
                    $builder->{$parameter} = $this->factory[$parameter]->createFieldBuilderList($builder, $value);
                    $fields = array_merge($fields, $builder->{$parameter});
                } else {
                    throw new LoaderException(sprintf('Undefined property `%s`', $parameter));
                }
            }
            try {
                $builder->identifiers = $this->normalizeIdentifiers($builder, $identifiers, $fields);
            } catch (LoaderException $e) {
                throw new LoaderException($e->getMessage().' in property identifier', 0, $e);
            }
            return $builder;
        } catch (LoaderException $e) {
            throw new LoaderException($e->getMessage().' in class '.$class, 0, $e);
        }
    }

    /**
     * @param MetadataBuilder $builder
     * @param array $identifiers
     * @param array $fields
     * @return array
     * @throws LoaderException
     */
    private function normalizeIdentifiers(MetadataBuilder $builder, $identifiers, array $fields)
    {
        if (is_array($identifiers)) {
            $list = [];
            foreach ($identifiers as $name => $value) {
                if ($value === null) {
                    $field = $fields[$name] ?? null;
                    if ($field === null) {
                        throw new LoaderException();
                    }
                } else {
                    $field = $this->factory['attributes']->createFieldBuilder($builder, (string) $name, $value);
                }
                $list[$name] = $field;
            }
            return $list;
        }
        throw new LoaderException();
    }

    /**
     * @param string[] $classes
     * @return string[][]
     */
    private function getInheritMap(array $classes): array
    {
        $map = [];
        foreach ($classes as $class) {
            $map[$class] = [];
            foreach ($classes as $inherit) {
                if ($class !== $inherit && is_a($class, $inherit, true)) {
                    $map[$class][] = $inherit;
                }
            }
        }
        return $map;
    }

    /**
     * @param array $list
     */
    private function sortInherit(array &$list)
    {
        uksort($list, function (string $left, string $right) {
            if ($left === $right) {
                return 0;
            } elseif (is_a($left, $right, true)) {
                return 1;
            } else {
                return -1;
            }
        });
    }
}
