<?php

namespace JsonApi\Metadata;

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
     * @var string|null
     */
    private $discriminatorAttribute = null;

    /**
     * @var Metadata[]
     */
    private $discriminatorMap = [];

    /**
     * @var Metadata[]
     */
    private $inherit = [];

    public function __construct(string $entityClass)
    {
        $this->class = $entityClass;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return FieldInterface[]
     */
    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    /**
     * @param FieldInterface $field
     */
    public function addIdentifier(FieldInterface $field): void
    {
        $this->identifiers[$field->getName()] = $field;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return FieldInterface[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param FieldInterface $field
     */
    public function addAttribute(FieldInterface $field): void
    {
        $this->attributes[$field->getName()] = $field;
    }

    /**
     * @return FieldInterface[]
     */
    public function getRelationships(): array
    {
        return $this->relationships;
    }

    /**
     * @param FieldInterface $field
     */
    public function addRelationship(FieldInterface $field): void
    {
        $this->relationships[$field->getName()] = $field;
    }

    public function setDiscriminatorAttribute(string $attribute)
    {
        $this->discriminatorAttribute = $attribute;
    }

    public function addDiscriminator(string $value, MetadataInterface $metadata)
    {
        $this->discriminatorMap[$value] = $metadata;
        $metadata->addInherit($this);
    }

    public function addInherit(MetadataInterface $metadata)
    {
        $this->inherit[] = $metadata;
    }

    public function setType(?string $type)
    {
        return $this->type = $type;
    }

    /**
     * @inheritDoc
     */
    public function __sleep()
    {
        $list = ['class'];
        if ($this->type !== null) {
            $list[] = 'type';
        }
        if ($this->identifiers) {
            $list[] = 'identifiers';
        }
        if ($this->attributes) {
            $list[] = 'attributes';
        }
        if ($this->relationships) {
            $list[] = 'relationships';
        }
        if ($this->discriminatorAttribute !== null) {
            $list[] = 'discriminatorAttribute';
        }
        if ($this->discriminatorMap) {
            $list[] = 'discriminatorMap';
        }
        if ($this->inherit) {
            $list[] = 'inherit';
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
