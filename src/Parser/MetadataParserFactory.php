<?php

namespace JsonApi\Parser;

use JsonApi\FieldNormalizer\Pool;
use JsonApi\Metadata\RegisterInterface;

/**
 * @package JsonApi\Parser
 */
class MetadataParserFactory
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @param Pool $pool
     */
    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    public function createMetadataParser(RegisterInterface $register)
    {
        return new MetadataParser(
            new AttributeFieldParser($this->pool),
            new RelationshipFieldParser($register)
        );
    }
}
