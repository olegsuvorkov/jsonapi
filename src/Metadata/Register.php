<?php

namespace JsonApi\Metadata;

class Register implements RegisterInterface
{
    /**
     * @var Metadata[]
     */
    private $relTypeToMetadata = [];

    /**
     * @var Metadata[]
     */
    private $relClassToMetadata = [];

    /**
     * @param MetadataInterface $metadata
     */
    public function add(MetadataInterface $metadata): void
    {
        $this->relClassToMetadata[$metadata->getClass()] = $metadata;
        if ($metadata->getType()) {
            $this->relTypeToMetadata[$metadata->getType()] = $metadata;
        }
    }

    /**
     * @param string $class
     * @return Metadata|null
     * @throws UndefinedMetadataException
     */
    public function getByClass(string $class): MetadataInterface
    {
        $metadata = $this->relClassToMetadata[$class] ?? null;
        if ($metadata) {
            return $metadata;
        }
        throw UndefinedMetadataException::notFindByClass($class);
    }

    /**
     * @param string $type
     * @return Metadata|null
     * @throws UndefinedMetadataException
     */
    public function getByType(string $type): MetadataInterface
    {
        $metadata = $this->relTypeToMetadata[$type] ?? null;
        if ($metadata) {
            return $metadata;
        }
        throw UndefinedMetadataException::notFindByType($type);
    }

    /**
     * @return Metadata[]
     */
    public function all(): array
    {
        return $this->relClassToMetadata;
    }
}
