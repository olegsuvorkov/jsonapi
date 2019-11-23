<?php

namespace JsonApi\Context;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
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
     * @var array
     */
    private $meta;

    /**
     * @param MetadataInterface $metadata
     * @param ContextIncludeInterface $include
     * @param RegisterInterface $register
     * @param array $meta
     */
    public function __construct(
        MetadataInterface $metadata,
        ContextIncludeInterface $include,
        RegisterInterface $register,
        array $meta
    ) {
        $this->metadata = $metadata;
        $this->register = $register;
        $this->include  = $include;
        $this->meta     = $meta;
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
     * @inheritDoc
     */
    public function hasType(string $type): bool
    {
        return $this->register->hasType($type);
    }

    /**
     * @return ContextIncludeInterface
     */
    public function getInclude(): ContextIncludeInterface
    {
        return $this->include;
    }

    /**
     * @param ContextIncludeInterface $include
     * @return Context
     */
    public function setInclude(ContextIncludeInterface $include): Context
    {
        $this->include = $include;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getEntityManager(): ObjectManager
    {
        return $this->metadata->getEntityManager();
    }

    /**
     * @inheritDoc
     */
    public function getRepository(): ObjectRepository
    {
        return $this->metadata->getRepository();
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->register;
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }
}
