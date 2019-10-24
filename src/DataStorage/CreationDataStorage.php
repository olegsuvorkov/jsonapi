<?php


namespace JsonApi\DataStorage;


use JsonApi\Metadata\MetadataInterface;
use JsonApi\Transformer\InvalidArgumentException;

class CreationDataStorage implements DataStorageInterface
{
    /**
     * @var DataStorageInterface
     */
    private $original;

    /**
     * @var object[][]
     */
    private $stack = [];

    /**
     * @param DataStorageInterface $original
     */
    public function __construct(DataStorageInterface $original)
    {
        $this->original = $original;
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
            return $this->original->get($metadata, $id);
        }
    }

    /**
     * @inheritDoc
     */
    public function isNew($object): bool
    {
        foreach ($this->stack as $list) {
            if (in_array($object, $list, true)) {
                return true;
            }
        }
        return $this->original->isNew($object);
    }
}
