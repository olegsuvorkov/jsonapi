<?php

namespace JsonApi\Metadata;

use JsonApi\SecurityStrategy\SecurityStrategyInterface;
use JsonApi\Transformer\InvalidArgumentException;

/**
 * @package JsonApi\Metadata\Field
 */
interface FieldInterface extends SecurityStrategyInterface
{
    /**
     * @param MetadataInterface $metadata
     * @param MetadataContainerInterface $container
     * @return void
     */
    public function initialize(MetadataInterface $metadata, MetadataContainerInterface $container): void;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getSerializeName(): string;

    /**
     * @param $object
     * @return mixed
     */
    public function getValue($object);

    /**
     * @param $object
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getNormalizeValue($object);

    public function reverseTransform(array $data);

    /**
     * @param array $data
     * @param array $options
     * @return object|object[]|null
     */
    public function denormalize(array $data, array $options);

    public function setDenormalizeValue($object, array $data, array $options): void;


    public function parseScalarValue(array &$data, array &$ids);

    /**
     * @param $object
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getScalarValue($object);

    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function getOption(string $name, $default = null);

    /**
     * @param array|null $fields
     * @return bool
     */
    public function inContext(?array &$fields): bool;

    /**
     * @return bool
     */
    public function isRelationship(): bool;

    /**
     * @return MetadataInterface
     */
    public function getTargetMetadata(): MetadataInterface;

    /**
     * @return bool
     */
    public function isManyToMany(): bool;

    /**
     * @return bool
     */
    public function isOneToMany(): bool;

    /**
     * @return bool
     */
    public function isManyToOne(): bool;

    /**
     * @return bool
     */
    public function isOneToOne(): bool;

    /**
     * @return bool
     */
    public function isToMany(): bool;

    /**
     * @return bool
     */
    public function isToOne(): bool;

    /**
     * @param object $entity
     * @return string
     */
    public function generateRelationshipUrl($entity): string;
}
