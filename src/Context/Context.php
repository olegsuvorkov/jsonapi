<?php

namespace JsonApi\Context;

use JsonApi\Metadata\MetadataInterface;
use JsonApi\Metadata\Register;
use JsonApi\Metadata\RegisterInterface;
use JsonApi\Metadata\UndefinedMetadataException;

/**
 * @package JsonApi
 */
class Context extends Register implements ContextInterface
{
    /**
     * @var RegisterInterface
     */
    private $original;

    /**
     * @var string[][]
     */
    public $fields = [];

    /**
     * @var string
     */
    private $metadata;

    /**
     * @param string $type
     * @param array $fields
     * @param RegisterInterface $original
     * @throws UndefinedMetadataException
     */
    public function __construct(string $type, array $fields, RegisterInterface $original)
    {
        parent::__construct([]);
        $this->fields = $fields;
        $this->original = $original;
        $this->metadata = $this->getByType($type);
    }

    /**
     * @return MetadataInterface
     */
    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }
}
