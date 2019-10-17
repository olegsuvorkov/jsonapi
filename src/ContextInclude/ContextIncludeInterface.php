<?php

namespace JsonApi\ContextInclude;

use JsonApi\Metadata\FieldInterface;
use JsonApi\Metadata\MetadataInterface;

/**
 * @package JsonApi\ContextInclude
 */
interface ContextIncludeInterface
{
    /**
     * @param MetadataInterface $metadata
     * @param $object
     * @param IncludeStackInterface $stack
     * @return void
     */
    public function register(MetadataInterface $metadata, $object, IncludeStackInterface $stack);

    /**
     * @param FieldInterface $field
     * @return ContextIncludeInterface|null
     */
    public function findBy(FieldInterface $field): ?ContextIncludeInterface;

    /**
     * @param ContextIncludeInterface $child
     * @return ContextIncludeInterface
     */
    public function add(ContextIncludeInterface $child): ContextIncludeInterface;
}
