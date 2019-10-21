<?php

namespace JsonApi\DataStorage;

use Doctrine\Common\Persistence\ManagerRegistry;
use JsonApi\Metadata\MetadataInterface;
use JsonApi\Transformer\InvalidArgumentException;

/**
 * @package JsonApi\DataStorage
 */
class DataStorage implements DataStorageInterface
{
    /**
     * @var ManagerRegistry
     */
    private $registryManager;

    /**
     * @var object[]
     */
    private $stack = [];

    /**
     * @param ManagerRegistry $registryManager
     */
    public function __construct(ManagerRegistry $registryManager)
    {
        $this->registryManager = $registryManager;
    }

    /**
     * @inheritDoc
     */
    public function get(MetadataInterface $metadata, string $id)
    {
        if ($metadata->isNew($id)) {
            $type = $metadata->getType();
            if (!isset($this->stack[$type])) {
                $this->stack[$type] = [];
            }
            if (!isset($this->stack[$type][$id])) {
                $this->stack[$type][$id] = $metadata->newInstanceWithoutConstructor();
            }
            return $this->stack[$type][$id];
        } else {
            $identifiers = $metadata->reverseId($id);
            $className   = $metadata->getClass();
            $em          = $this->registryManager->getManagerForClass($className);
            $entity      = $em->find($className, $identifiers);
            if ($entity) {
                return $entity;
            }
            throw new InvalidArgumentException();
        }
    }

    public function isNew($object): bool
    {
        foreach ($this->stack as $list) {
            if (in_array($object, $list, true)) {
                return true;
            }
        }
        return false;
    }

    public function set(MetadataInterface $metadata, string $id, $data): void
    {
        if (!isset($this->stack[$metadata->getType()])) {
            $this->stack[$metadata->getType()] = [];
        }
        $this->stack[$metadata->getType()][$id] = $data;
    }
}
