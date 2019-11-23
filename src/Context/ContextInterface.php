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
interface ContextInterface extends RegisterInterface
{
    /**
     * @return MetadataInterface
     */
    public function getMetadata(): MetadataInterface;

    /**
     * @return ContextIncludeInterface
     */
    public function getInclude(): ContextIncludeInterface;

    /**
     * @return ObjectManager
     */
    public function getEntityManager(): ObjectManager;

    /**
     * @return ObjectRepository
     */
    public function getRepository(): ObjectRepository;

    /**
     * @return string[]
     */
    public function getMeta(): array;
}
