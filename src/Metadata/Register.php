<?php

namespace JsonApi\Metadata;

class Register implements RegisterInterface
{
    /**
     * @var Metadata[]
     */
    protected $map = [];

    /**
     * @param array $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * @param string $class
     * @return Metadata|null
     * @throws UndefinedMetadataException
     */
    public function getByClass($class): MetadataInterface
    {
        foreach ($this->map as $metadata) {
            if ($metadata->isInstance($class)) {
                return $metadata;
            }
        }
        throw UndefinedMetadataException::notFindByClass(is_object($class) ? get_class($class) : $class);
    }

    /**
     * @param string $type
     * @return Metadata|null
     * @throws UndefinedMetadataException
     */
    public function getByType(string $type): MetadataInterface
    {
        $metadata = $this->map[$type] ?? null;
        if ($metadata) {
            return $metadata;
        }
        throw UndefinedMetadataException::notFindByType($type);
    }

    /**
     * @inheritDoc
     */
    public function hasType(string $type): bool
    {
        return isset($this->map[$type]);
    }
}
