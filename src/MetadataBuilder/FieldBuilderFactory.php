<?php

namespace JsonApi\MetadataBuilder;

use JsonApi\MetadataBuilder\Configurator\ConfiguratorInterface;

/**
 * @package JsonApi\MetadataBuilder
 */
class FieldBuilderFactory implements FieldBuilderFactoryInterface
{
    /**
     * @var ConfiguratorInterface
     */
    private $configurator;

    /**
     * @param \JsonApi\MetadataBuilder\Configurator\ConfiguratorInterface $configurator
     */
    public function __construct(ConfiguratorInterface $configurator)
    {
        $this->configurator = $configurator;
    }

    /**
     * @inheritDoc
     */
    public function createFieldBuilderList(MetadataBuilder $builder, $fields): array
    {
        if (is_array($fields)) {
            $list = [];
            foreach ($fields as $name => $parameters) {
                $list[$name] = $this->createFieldBuilder($builder, (string) $name, $parameters);
            }
            return $list;
        }
        throw new BuilderException('Invalid fields');
    }

    /**
     * @inheritDoc
     */
    public function createIdentifierBuilderList(MetadataBuilder $builder, $fields): array
    {
        if (is_array($fields)) {
            $list = [];
            foreach ($fields as $name => $value) {
                if ($value === null) {
                    $field = $builder->attributes[$name] ?? $builder->relationships[$name] ?? null;
                    if ($field === null) {
                        throw new BuilderException();
                    }
                } else {
                    $field = $this->createFieldBuilder($builder, $name, $value);
                }
                $list[$name] = $field;
            }
            return $list;
        }
        throw new BuilderException();
    }

    /**
     * @inheritDoc
     */
    public function createConstructorArgumentsBuilderList(MetadataBuilder $builder, $fields): array
    {
        if (is_array($fields)) {
            $list = [];
            foreach ($fields as $name) {
                $field = $builder->attributes[$name] ?? $builder->relationships[$name] ?? null;
                if ($field !== null) {
                    $list[$name] = $field;
                } else {
                    throw new BuilderException();
                }
            }
            return $list;
        }
        throw new BuilderException();
    }

    /**
     * @param MetadataBuilder $metadataBuilder
     * @param $name
     * @param $parameters
     * @return FieldBuilder
     * @throws BuilderException
     */
    private function createFieldBuilder(MetadataBuilder $metadataBuilder, string $name, $parameters): FieldBuilder
    {
        try {
            if (false === preg_match('~^[a-zA-Z0-9_]$~', $name)) {
                throw new BuilderException('Invalid name');
            }
            if (!is_array($parameters)) {
                throw new BuilderException('Invalid properties expected array');
            }
            $builder = new FieldBuilder($name, $this->configurator, $metadataBuilder);
            foreach ($parameters as $key => $value) {
                $this->buildProperty($builder, (string) $key, $value);
            }
            return $builder;
        } catch (BuilderException $e) {
            throw BuilderException::invalidField($name, $e);
        }
    }

    /**
     * @param FieldBuilder $builder
     * @param string $key
     * @param mixed $value
     * @throws BuilderException
     */
    private function buildProperty(FieldBuilder $builder, string $key, $value): void
    {
        if ($key === 'context') {
            if (is_bool($value)) {
                $builder->context = $value;
            } else {
                throw BuilderException::invalidPropertyExpected('context', 'bool');
            }
        } elseif ($key === 'read') {
            if (is_bool($value)) {
                $builder->read = $value;
            } else {
                throw BuilderException::invalidPropertyExpected('read', 'bool');
            }
        } elseif ($key === 'write') {
            if (is_bool($value)) {
                $builder->write = $value;
            } else {
                throw BuilderException::invalidPropertyExpected('write', 'bool');
            }
        } elseif ($key === 'getter') {
            if (is_string($value)) {
                $builder->getter = $value;
            } else {
                throw BuilderException::invalidPropertyExpected('getter', 'string');
            }
        } elseif ($key === 'setter') {
            if (is_string($value)) {
                $builder->setter = $value;
            } else {
                throw BuilderException::invalidPropertyExpected('setter', 'string');
            }
        } else {
            $builder->options[$key] = $value;
        }
    }
}
