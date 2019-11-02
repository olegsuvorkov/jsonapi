<?php


namespace JsonApi\Metadata;

/**
 * @package JsonApi\Metadata
 */
class ContextRegister extends Register
{
    /**
     * @var string[][]
     */
    private $fields;

    /**
     * @var RegisterInterface
     */
    private $original;

    /**
     * @param string[][] $fields
     * @param RegisterInterface $original
     */
    public function __construct(array $fields, RegisterInterface $original)
    {
        parent::__construct([]);
        $this->fields = $fields;
        $this->original = $original;
    }

    /**
     * @inheritDoc
     */
    public function getByClass($class): MetadataInterface
    {
        try {
            return parent::getByClass($class);
        } catch (UndefinedMetadataException $e) {
            return $this->registerContextMetadata($this->original->getByClass($class));
        }
    }

    /**
     * @inheritDoc
     */
    public function getByType(string $type): MetadataInterface
    {
        try {
            return parent::getByType($type);
        } catch (UndefinedMetadataException $e) {
            return $this->registerContextMetadata($this->original->getByType($type));
        }
    }

    private function registerContextMetadata(MetadataInterface $metadata): MetadataInterface
    {
        $metadata = $metadata->createContextMetadata($this->getFields($metadata));
        $this->map[$metadata->getType()] = $metadata;
        return $metadata;
    }

    /**
     * @param MetadataInterface $item
     * @return string[]|null
     */
    private function getFields(MetadataInterface $item): ?array
    {
        do {
            $fields = $this->fields[$item->getType()] ?? null;
            if ($fields !== null) {
                return $fields;
            }
        } while ($item = $item->getParent());
        return null;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->original->jsonSerialize();
    }
}
