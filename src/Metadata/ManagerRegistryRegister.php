<?php

namespace JsonApi\Metadata;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\Mapping\MappingException;

/**
 * @package JsonApi\Metadata
 */
class ManagerRegistryRegister implements RegisterInterface
{
    /**
     * @var RegisterInterface
     */
    private $original;

    /**
     * @var ManagerRegistry|null
     */
    private $managerRegistry;

    /**
     * @var string[]
     */
    private $map = [];

    /**
     * @param RegisterInterface $original
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(RegisterInterface $original, ManagerRegistry $managerRegistry = null)
    {
        $this->original = $original;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @inheritDoc
     */
    public function add(MetadataInterface $metadata): void
    {
        $this->original->add($metadata);
    }

    /**
     * @inheritDoc
     */
    public function getByClass(string $class): MetadataInterface
    {
        if ($this->managerRegistry) {
            $class = $this->detectOriginalClass($class);
        }
        return $this->original->getByClass($class);
    }

    /**
     * @inheritDoc
     */
    public function getByType(string $type): MetadataInterface
    {
        return $this->original->getByType($type);
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->original->all();
    }

    private function detectOriginalClass(string $class)
    {
        if (isset($this->map[$class])) {
            return $this->map[$class];
        } elseif (is_a($class, Proxy::class, true)) {
            $em = $this->managerRegistry->getManagerForClass($class);
            if ($em) {
                try {
                    $meta = $em->getClassMetadata($class);
                    return $this->map[$class] = $meta->getName();
                } catch (MappingException $e) {
                }
            }
        }
        return $class;
    }
}