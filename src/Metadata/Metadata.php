<?php

namespace JsonApi\Metadata;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use JsonApi\SecurityStrategy\SecurityStrategyInterface;
use JsonApi\Transformer\InvalidArgumentException;
use ReflectionClass;

/**
 * @package JsonApi\ClassMetadata
 */
class Metadata implements MetadataInterface
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var FieldInterface[]
     */
    private $identifiers = [];

    /**
     * @var FieldInterface[]
     */
    private $attributes = [];

    /**
     * @var FieldInterface[]
     */
    private $relationships = [];

    /**
     * @var MetadataInterface[]
     */
    private $discriminatorMap = [];

    /**
     * @var MetadataInterface|null
     */
    private $parent;

    /**
     * @var SecurityStrategyInterface
     */
    private $security;

    /**
     * @var string
     */
    private $securityStrategy;

    /**
     * @var array
     */
    private $securityOptions;

    /**
     * @var SecurityStrategyInterface
     */
    private $securityNormalize;

    /**
     * @var string
     */
    private $securityNormalizeStrategy;

    /**
     * @var array
     */
    private $securityNormalizeOptions;

    /**
     * @var FieldInterface[]
     */
    private $constructorArguments = [];

    /**
     * @var MetadataInterface|null
     */
    private $original = null;

    /**
     * @var MetadataContainerInterface
     */
    private $container;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param string $class
     * @param string $type
     * @param string $securityStrategy
     * @param array $securityOptions
     * @param string $securityNormalizeStrategy
     * @param array $securityNormalizeOptions
     */
    public function __construct(
        string $class,
        string $type,
        string $securityStrategy,
        array $securityOptions,
        string $securityNormalizeStrategy,
        array $securityNormalizeOptions
    ) {
        $this->class = $class;
        $this->type = $type;
        $this->securityStrategy = $securityStrategy;
        $this->securityOptions = $securityOptions;
        $this->securityNormalizeStrategy = $securityNormalizeStrategy;
        $this->securityNormalizeOptions = $securityNormalizeOptions;
    }

    /**
     * @inheritDoc
     */
    public function initialize(MetadataContainerInterface $metadataContainer): void
    {
        $this->container = $metadataContainer;
        foreach ($this->identifiers as $field) {
            $field->initialize($this, $metadataContainer);
        }
        foreach ($this->attributes as $field) {
            if (!in_array($field, $this->identifiers, true)) {
                $field->initialize($this, $metadataContainer);
            }
        }
        foreach ($this->relationships as $field) {
            if (!in_array($field, $this->identifiers, true)) {
                $field->initialize($this, $metadataContainer);
            }
        }
    }

    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function getConstructorArguments(): array
    {
        return $this->constructorArguments;
    }

    private function getReflection(): ReflectionClass
    {
        return $this->getClassMetadata()->getReflectionClass();
    }

    public function getClassMetadata(): ClassMetadata
    {
        return $this->getEntityManager()->getClassMetadata($this->class);
    }

    /**
     * @param FieldInterface[] $constructorArguments
     */
    public function setConstructorArguments(array $constructorArguments): void
    {
        $this->constructorArguments = $constructorArguments;
    }

    public static function reverseRelatedTransform(array $data)
    {
        $id = null;
        $type = null;
        foreach (array_merge(['type' => null], $data) as $key => $value) {
            if ($key === 'id') {
                if (is_string($value)) {
                    $id = $value;
                } else {
                    throw new InvalidArgumentException();
                }
            } elseif ($key === 'type') {
                if (is_string($value)) {
                    $type = $value;
                } else {
                    throw new InvalidArgumentException();
                }
            }
        }
        return [$id, $type];
    }

    /**
     * @inheritDoc
     */
    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    /**
     * @param FieldInterface[] $identifiers
     */
    public function setIdentifiers(array $identifiers): void
    {
        $this->identifiers = $identifiers;
    }

    /**
     * @inheritDoc
     */
    public function getDiscrimination(): array
    {
        return $this->discriminatorMap;
    }

    /**
     * @inheritDoc
     */
    public function getOriginalMetadata($object): MetadataInterface
    {
        foreach ($this->discriminatorMap as $discrimination) {
            if ($discrimination->isInstance($object)) {
                return $discrimination->getOriginalMetadata($object);
            }
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMetadataByType(string $type): MetadataInterface
    {
        foreach ($this->discriminatorMap as $discrimination) {
            if ($discrimination->getType() === $type) {
                return $discrimination;
            }
        }
        if ($this->type === $type) {
            return $this;
        }
        throw new InvalidArgumentException();
    }

    /**
     * @return object
     */
    public function newInstanceWithoutConstructor()
    {
        return $this->getReflection()->newInstanceWithoutConstructor();
    }

    /**
     * @inheritDoc
     */
    public function invokeConstructor($object, array $arguments = [])
    {
        $method = $this->getReflection()->getConstructor();
        if ($method) {
            $method->invokeArgs($object, $arguments);
        }
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @inheritDoc
     */
    public function getDenormalizedAttributes()
    {
        foreach ($this->attributes as $field) {
            if (!in_array($field, $this->constructorArguments, true)) {
                yield $field;
            }
        }
    }

    /**
     * @param FieldInterface[] $attributes
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @inheritDoc
     */
    public function getRelationships(): array
    {
        return $this->relationships;
    }

    /**
     * @inheritDoc
     */
    public function getDenormalizedRelationships()
    {
        foreach ($this->relationships as $field) {
            if (!in_array($field, $this->constructorArguments, true)) {
                yield $field;
            }
        }
    }

    public function findRelationships(string $serializeName)
    {
        if ($this->discriminatorMap) {
            foreach ($this->discriminatorMap as $discrimination) {
                yield from $discrimination->findRelationships($serializeName);
            }
        } else {
            foreach ($this->relationships as $relationship) {
                if ($relationship->getSerializeName() === $serializeName) {
                    yield $relationship;
                    break;
                }
            }
        }
    }

    public function getRelationship(string $serializeName): FieldInterface
    {
        foreach ($this->findRelationships($serializeName) as $field) {
            return $field;
        }
        throw new InvalidArgumentException();
    }

    /**
     * @inheritDoc
     */
    public function getRelationshipByEntity($object, string $serializeName): FieldInterface
    {
        return $this->getOriginalMetadata($object)->getRelationship($serializeName);
    }

    /**
     * @inheritDoc
     */
    public function containsRelationship(FieldInterface $field): bool
    {
        return in_array($field, $this->relationships, true);
    }

    /**
     * @param FieldInterface[] $relationships
     */
    public function setRelationships(array $relationships): void
    {
        $this->relationships = $relationships;
    }

    /**
     * @param MetadataInterface $metadata
     */
    public function addDiscriminator(MetadataInterface $metadata)
    {
        $metadata->setParent($this);
        $this->discriminatorMap[] = $metadata;
    }

    /**
     * @inheritDoc
     */
    public function getParent(): ?MetadataInterface
    {
        return $this->parent;
    }

    /**
     * @inheritDoc
     */
    public function setParent(?MetadataInterface $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @inheritDoc
     */
    public function isInstance($object): bool
    {
        return is_a($object, $this->class, true) && empty($this->discriminatorMap);
    }

    /**
     * @inheritDoc
     */
    public function getId($object): string
    {
        $id = '';
        foreach ($this->identifiers as $i => $identifier) {
            $id.= $i ? ':' : '';
            $id.= $identifier->getScalarValue($object);
        }
        return $id;
    }

    /**
     * @inheritDoc
     */
    public function reverseId($id): array
    {
        $ids = explode(':', $id);
        return $this->reverseTransformId($ids);
    }

    public function isNew(string $id, int &$length = null): bool
    {
        $length = count($this->identifiers);
        return 0 === strncmp(str_repeat(':', $length), $id, $length) && strlen($id) > $length;
    }

    /**
     * @inheritDoc
     */
    public function getNewIdentifier(string $id): ?int
    {
        return $this->isNew($id, $length) ? intval(substr($id, $length)) : null;
    }

    /**
     * @inheritDoc
     */
    public function reverseTransformId(array &$ids): array
    {
        $data = [];
        foreach ($this->identifiers as $i => $identifier) {
            $identifier->parseScalarValue($data, $ids);
        }
        return $data;
    }

    /**
     * @return SecurityStrategyInterface
     */
    public function getSecurity(): SecurityStrategyInterface
    {
        if ($this->security === null) {
            $this->security = $this->container->buildSecurityStrategy(
                $this->securityStrategy,
                $this->securityOptions
            );
        }
        return $this->security;
    }

    /**
     * @return SecurityStrategyInterface
     */
    public function getSecurityNormalize(): SecurityStrategyInterface
    {
        if ($this->securityNormalize === null) {
            $this->securityNormalize = $this->container->buildSecurityStrategy(
                $this->securityNormalizeStrategy,
                $this->securityNormalizeOptions
            );
        }
        return $this->securityNormalize;
    }

    /**
     * @return MetadataInterface
     */
    public function getOriginal(): MetadataInterface
    {
        return $this->original ?? $this;
    }

    /**
     * @inheritDoc
     */
    public function createContextMetadata(?array $fields): MetadataInterface
    {
        $metadata = clone $this;
        $metadata->original = $this;
        $metadata->attributes = [];
        foreach ($this->attributes as $field) {
            if ($field->inContext($fields)) {
                $metadata->attributes[] = $field;
            }
        }
        $metadata->relationships = [];
        foreach ($this->relationships as $field) {
            if ($field->inContext($fields)) {
                $metadata->relationships[] = $field;
            }
        }
        return $metadata;
    }

    /**
     * @inheritDoc
     */
    public function isGranted(string $attribute, $subject = null): bool
    {
        return $this->getSecurity()->isGranted($attribute, $subject);
    }

    /**
     * @inheritDoc
     */
    public function denyAccessUnlessGranted(string $attribute, $subject = null): void
    {
        $this->getSecurity()->denyAccessUnlessGranted($attribute, $subject);
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function find(string $id)
    {
        return $this->getEntityManager()->find($this->class, $this->reverseId($id));
    }

    /**
     * @inheritDoc
     */
    public function getEntityManager(): ObjectManager
    {
        if ($this->em === null) {
            $this->em = $this->container->getEntityManager($this->class);
        }
        return $this->em;
    }

    public function getRepository(): ObjectRepository
    {
        return $this->getEntityManager()->getRepository($this->class);
    }

    /**
     * @inheritDoc
     */
    public function generateListUrl(): string
    {
        return $this->container->generateUrl($this->type);
    }

    /**
     * @inheritDoc
     */
    public function generateEntityUrl($entity): string
    {
        return $this->container->generateEntityUrl($this->type, $this->getId($entity));
    }

    /**
     * @inheritDoc
     */
    public function __sleep()
    {
        $list = ['class', 'type'];
        if ($this->identifiers) {
            $list[] = 'identifiers';
        }
        if ($this->attributes) {
            $list[] = 'attributes';
        }
        if ($this->relationships) {
            $list[] = 'relationships';
        }
        if ($this->discriminatorMap) {
            $list[] = 'discriminatorMap';
        }
        return $list;
    }

    /**
     * @inheritDoc
     */
    public function __wakeup()
    {

    }
}
