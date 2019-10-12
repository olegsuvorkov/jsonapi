<?php

namespace JsonApi\MetadataBuilder;

use JsonApi\Exception\LoaderException;
use JsonApi\TransformerConfigurator\TransformerConfiguratorInterface;

/**
 * @package JsonApi\MetadataBuilder
 */
class FieldBuilderFactory implements FieldBuilderFactoryInterface
{
    /**
     * @var TransformerConfiguratorInterface
     */
    private $configurator;

    /**
     * @param TransformerConfiguratorInterface $configurator
     */
    public function __construct(TransformerConfiguratorInterface $configurator)
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
        throw new LoaderException('Invalid fields');
    }

    /**
     * @inheritDoc
     */
    public function createFieldBuilder(MetadataBuilder $metadataBuilder, string $name, $parameters): FieldBuilder
    {
        try {
            if (false === preg_match('~^[a-zA-Z0-9_]$~', $name)) {
                throw new LoaderException('Expected valid field name');
            }
            if (!is_array($parameters)) {
                throw new LoaderException('Invalid parameters');
            }
            $builder = new FieldBuilder($name, $this->configurator, $metadataBuilder);
            foreach ($parameters as $key => $value) {
                if ($key === 'context' || $key === 'read' || $key === 'write') {
                    if (is_bool($value)) {
                        $builder->{$key} = $value;
                    } else {
                        throw LoaderException::expectedFieldProperty($name, $key, 'bool');
                    }
                } elseif ($key === 'getter' || $key === 'setter') {
                    if (is_string($value)) {
                        $builder->{$key} = $value;
                    } else {
                        throw LoaderException::expectedFieldProperty($name, $key, 'string');
                    }
                } else {
                    $builder->options[$key] = $value;
                }
            }
            return $builder;
        } catch (LoaderException $e) {
            throw LoaderException::wrapInClass($e, $metadataBuilder->class);
        }
    }
}
