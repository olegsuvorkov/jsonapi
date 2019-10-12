<?php

namespace JsonApi\Metadata;

use JsonApi\Transformer\TransformerPool;

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
     * @var Metadata[]
     */
    private $discriminatorMap = [];

    /**
     * @var Metadata|null
     */
    private $parent;

    /**
     * @param string $class
     * @param string $type
     */
    public function __construct(string $class, string $type)
    {
        $this->class = $class;
        $this->type  = $type;
    }

    public function getClass(): string
    {
        return $this->class;
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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function getTypeByObject($object): string
    {
        foreach ($this->discriminatorMap as $discrimination) {
            if ($discrimination->isInstance($object)) {
                return $discrimination->getTypeByObject($object);
            }
        }
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return $this->attributes;
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
        return is_a($object, $this->class, true);
    }

    /**
     * @inheritDoc
     */
    public function getId($object, TransformerPool $pool): string
    {
        $id = '';
        foreach ($this->identifiers as $identifier) {
            $id.= ':'.($identifier->getScalarValue($object, $pool) ?? '');
        }
        return substr($id, 1);
    }

    /**
     * @inheritDoc
     */
    public function createContextMetadata(?array $fields): MetadataInterface
    {
        $metadata = clone $this;
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
