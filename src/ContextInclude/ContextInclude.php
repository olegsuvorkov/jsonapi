<?php

namespace JsonApi\ContextInclude;

use JsonApi\Metadata\FieldInterface;
use JsonApi\Metadata\MetadataInterface;

/**
 * @package JsonApi\ContextInclude
 */
class ContextInclude implements ContextIncludeInterface
{
    /**
     * @var ContextInclude[]
     */
    private $list = [];

    /**
     * @var FieldInterface
     */
    public $field;

    /**
     * @param FieldInterface|null $field
     */
    public function __construct(FieldInterface $field = null)
    {
        $this->field = $field;
    }

    public function register(MetadataInterface $metadata, $object, array &$stack)
    {
        $metadata = $metadata->getOriginalMetadata($object);
        foreach ($this->list as $field) {
            if ($metadata->containsRelationship($field->field)) {
                $field->addToStack($object, $stack);
            }
        }
    }

    public function findBy(FieldInterface $field): ?ContextIncludeInterface
    {
        foreach ($this->list as $item) {
            if ($item->field === $field) {
                return $item;
            }
        }
        return null;
    }

    public function add(ContextIncludeInterface $child): ContextIncludeInterface
    {
        return $this->list[] = $child;
    }

    protected function addToStack($data, array &$stack)
    {
        $data = $this->field->getValue($data);
        if ($data) {
            if (!in_array($data, $stack, true)) {
                $stack[] = $data;
            }
            $this->register($this->field->getOption('target'), $data, $stack);
        }
    }
}
