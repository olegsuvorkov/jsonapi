<?php

namespace JsonApi\Context;

use JsonApi\ContextInclude\ContextIncludeInterface;
use JsonApi\Metadata\MetadataInterface;
use JsonApi\Metadata\RegisterInterface;

/**
 * @package JsonApi
 */
class Context implements ContextInterface
{
    /**
     * @var RegisterInterface
     */
    private $register;

    /**
     * @var string
     */
    private $metadata;

    /**
     * @var ContextIncludeInterface
     */
    private $include;

    /**
     * @param MetadataInterface $metadata
     * @param ContextIncludeInterface $include
     * @param RegisterInterface $register
     */
    public function __construct(MetadataInterface $metadata, ContextIncludeInterface $include, RegisterInterface $register)
    {
        $this->metadata = $metadata;
        $this->register = $register;
        $this->include = $include;
    }

    /**
     * @return MetadataInterface
     */
    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }

    /**
     * @inheritDoc
     */
    public function getByClass($class): MetadataInterface
    {
        return $this->register->getByClass($class);
    }

    /**
     * @inheritDoc
     */
    public function getByType(string $type): MetadataInterface
    {
        return $this->register->getByType($type);
    }

    /**
     * @return ContextIncludeInterface
     */
    public function getInclude(): ContextIncludeInterface
    {
        return $this->include;
    }
}
