<?php

namespace JsonApi\Metadata;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use JsonApi\SecurityStrategy\SecurityStrategyInterface;
use JsonApi\Transformer\InvalidArgumentException;

/**
 * @package JsonApi\ClassMetadata
 */
interface MetadataInterface extends SecurityStrategyInterface
{
    /**
     * @param MetadataContainerInterface $metadataContainer
     */
    public function initialize(MetadataContainerInterface $metadataContainer): void;

    /**
     * @return string
     */
    public function getClass(): string;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return FieldInterface[]
     */
    public function getConstructorArguments(): array;

    /**
     * @return ClassMetadata
     */
    public function getClassMetadata(): ClassMetadata;

    /**
     * @return MetadataInterface[]
     */
    public function getDiscrimination(): array;

    /**
     * @param $object
     * @return MetadataInterface
     */
    public function getOriginalMetadata($object): MetadataInterface;

    /**
     * @param string $type
     * @return MetadataInterface
     */
    public function getMetadataByType(string $type): MetadataInterface;

    /**
     * @return object
     */
    public function newInstanceWithoutConstructor();

    /**
     * @param $object
     * @param array $arguments
     * @return void
     */
    public function invokeConstructor($object, array $arguments = []);

    /**
     * @return FieldInterface[]
     */
    public function getIdentifiers(): array;

    /**
     * @return FieldInterface[]
     */
    public function getAttributes(): array;

    /**
     * @return FieldInterface[]
     */
    public function getDenormalizedAttributes();

    /**
     * @return FieldInterface[]
     */
    public function getRelationships(): array;

    /**
     * @return FieldInterface[]
     */
    public function getDenormalizedRelationships();

    /**
     * @param string $serializeName
     * @return FieldInterface[]
     */
    public function findRelationships(string $serializeName);

    /**
     * @param string $serializeName
     * @return FieldInterface
     */
    public function getRelationship(string $serializeName): FieldInterface;

    /**
     * @param $object
     * @param string $serializeName
     * @return FieldInterface
     */
    public function getRelationshipByEntity($object, string $serializeName): FieldInterface;

    /**
     * @param FieldInterface $field
     * @return bool
     */
    public function containsRelationship(FieldInterface $field): bool;

    /**
     * @param $object
     * @return string
     * @throws InvalidArgumentException
     */
    public function getId($object): string;

    /**
     * @param string $id
     * @return array
     */
    public function reverseId($id): array;

    /**
     * @param string $id
     * @param int|null $length
     * @return bool
     */
    public function isNew(string $id, int &$length = null): bool;

    /**
     * @param string $id
     * @return int|null
     */
    public function getNewIdentifier(string $id): ?int;

    /**
     * @param array $ids
     * @return array
     */
    public function reverseTransformId(array &$ids): array;

    /**
     * @return SecurityStrategyInterface
     */
    public function getSecurity(): SecurityStrategyInterface;

    /**
     * @return SecurityStrategyInterface
     */
    public function getSecurityNormalize(): SecurityStrategyInterface;

    /**
     * @return MetadataInterface|null
     */
    public function getParent(): ?MetadataInterface;

    /**
     * @param MetadataInterface|null $parent
     */
    public function setParent(?MetadataInterface $parent): void;

    /**
     * @return MetadataInterface
     */
    public function getOriginal(): MetadataInterface;

    /**
     * @param string[] $fields
     * @return MetadataInterface
     */
    public function createContextMetadata(?array $fields): MetadataInterface;

    /**
     * @param object|string $object
     * @return bool
     */
    public function isInstance($object): bool;

    /**
     * @param string $id
     * @return mixed
     */
    public function find(string $id);

    /**
     * @return ObjectManager
     */
    public function getEntityManager(): ObjectManager;

    /**
     * @return ObjectRepository
     */
    public function getRepository(): ObjectRepository;

    /**
     * @return string
     */
    public function generateListUrl(): string;

    /**
     * @param $entity
     * @return string
     */
    public function generateEntityUrl($entity): string;
}
