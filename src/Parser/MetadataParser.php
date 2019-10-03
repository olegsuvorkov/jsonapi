<?php

namespace JsonApi\Parser;

use JsonApi\Exception\LoaderException;
use JsonApi\Metadata\Field;
use JsonApi\Metadata\MetadataInterface;
use ReflectionClass;
use ReflectionException;

/**
 * @package JsonApi\Parser
 */
class MetadataParser implements MetadataParserInterface
{
    /**
     * @var FieldParserInterface
     */
    private $attributeFactory;

    /**
     * @var FieldParserInterface
     */
    private $relationshipFactory;

    /**
     * @param FieldParserInterface $attributeFactory
     * @param FieldParserInterface $relationshipFactory
     */
    public function __construct(FieldParserInterface $attributeFactory, FieldParserInterface $relationshipFactory)
    {
        $this->attributeFactory    = $attributeFactory;
        $this->relationshipFactory = $relationshipFactory;
    }

    /**
     * @inheritDoc
     * @throws LoaderException
     * @throws ReflectionException
     */
    public function parseMetadata(MetadataInterface $metadata, array $parameters): void
    {
        $identifiers   = [];
        $normalized    = [
            'identifiers'   => [],
            'attributes'    => [],
            'relationships' => [],
        ];
        $fields = [];
        $reflectionClass = new ReflectionClass($metadata->getClass());

        foreach ($parameters as $parameter => $value) {
            if ($parameter === 'identifiers') {
                if (is_array($value)) {
                    $identifiers = $value;
                } else {
                    throw new LoaderException();
                }
            } elseif ($parameter === 'attributes' || $parameter === 'relationships') {
                if (is_array($value)) {
                    foreach ($value as $name => $params) {
                        $field = $this->createField($name, $params, $reflectionClass);
                        $name  = $field->getName();
                        if (isset($fields[$name])) {
                            throw new LoaderException();
                        }
                        $fields[$name] = $field;
                        $normalized[$parameter][$name] = $params;
                    }
                } else {
                    throw new LoaderException();
                }
            } elseif ($parameter === 'meta') {
            } else {
                throw new LoaderException(sprintf('Invalid metadata property `%s`', $parameter));
            }
        }
        foreach ($identifiers as $name => $identifier) {
            if ($identifier === null) {
                if (isset($fields[$name])) {
                    $field = $fields[$name];
                    if (!$field->getGetter()) {
                        throw new LoaderException();
                    }
                    $normalized['identifiers'][$name] = null;
                } else {
                    throw new LoaderException(sprintf(
                        'Not defined field `%s` in metadata for class `%s`',
                        $name,
                        $reflectionClass->getName()
                    ));
                }
            } else {
                $field = $this->createField($name, $identifier, $reflectionClass);
                if (!$field->getGetter()) {
                    throw new LoaderException();
                }
                $fields[$name] = $field;
                $normalized['identifiers'][$name] = $identifier;
            }
        }
        foreach ($normalized as $property => $props) {
            if ($property === 'identifiers') {
                foreach ($props as $name => $parameters) {
                    $field = $fields[$name];
                    if ($parameters !== null) {
                        $this->attributeFactory->parseField($field, $parameters);
                    }
                    $metadata->addIdentifier($field);
                }
            } elseif ($property === 'attributes') {
                ksort($props);
                foreach ($props as $name => $parameters) {
                    $field = $fields[$name];
                    $this->attributeFactory->parseField($field, $parameters);
                    $metadata->addAttribute($field);
                }
            } elseif ($property === 'relationships') {
                ksort($props);
                foreach ($props as $name => $parameters) {
                    $field = $fields[$name];
                    $this->relationshipFactory->parseField($field, $parameters);
                    $metadata->addRelationship($field);
                }
            }
        }
    }

    /**
     * @param string $name
     * @param array $parameters
     * @param ReflectionClass $reflectionClass
     * @return Field
     * @throws LoaderException
     */
    private function createField($name, array &$parameters, ReflectionClass $reflectionClass)
    {
        if (!is_string($name) || false === preg_match('~^[a-zA-Z0-9_]$~', $name)) {
            throw new LoaderException();
        }
        $field = new Field($name);
        $field->setGetter(
            $this->getMethod($reflectionClass, 'read', 'getter', $parameters, $name, ['get', 'is', 'has', 'getIs'])
        );
        $field->setSetter(
            $this->getMethod($reflectionClass, 'write', 'setter', $parameters, $name, ['set', 'setIs', 'setHas'])
        );
        return $field;
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param string $accessKey
     * @param string $methodKey
     * @param array $parameters
     * @param string $name
     * @param array $prefixes
     * @return string|null
     * @throws LoaderException
     */
    private function getMethod(
        ReflectionClass $reflectionClass,
        string $accessKey,
        string $methodKey,
        array &$parameters,
        string $name,
        array $prefixes
    ): ?string {
        $method = $parameters[$methodKey] ?? $this;
        unset($parameters[$methodKey]);
        $allow = $parameters[$accessKey] ?? true;
        unset($parameters[$accessKey]);
        if (!is_bool($allow)) {
            throw new LoaderException();
        }
        if (!$allow) {
            return null;
        }
        if ($method === $this) {
            return $this->detectMethod($reflectionClass, $methodKey, $name, $prefixes);
        } elseif (is_string($method)) {
            if ($reflectionClass->hasMethod($method)) {
                return $method;
            } else {
                throw new LoaderException();
            }
        } else {
            throw new LoaderException();
        }
    }

    private function detectMethod(ReflectionClass $reflectionClass, string $type, string $name, array $prefixes)
    {
//        $list = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
//        dump($list);die;
        $suffix = ucfirst($name);
        foreach ($prefixes as $prefix) {
            if ($reflectionClass->hasMethod($prefix.$suffix)) {
                return $prefix.$suffix;
            }
        }
        throw new LoaderException(sprintf(
            'Not find %s method for field `%s` in class `%s`',
            $type,
            $name,
            $reflectionClass->getName()
        ));
    }
}
