<?php

namespace JsonApi\Loader;

use JsonApi\MetadataBuilder\MetadataBuilderFactory;
use JsonApi\Parser\ParserInterface;

/**
 * @package JsonApi\Loader
 */
class ParserLoader implements LoaderInterface
{
    /**
     * @var ParserInterface[]
     */
    private $parsers;

    /**
     * @var MetadataBuilderFactory
     */
    private $metadataBuilderFactory;

    /**
     * @param ParserInterface[] $parsers
     * @param MetadataBuilderFactory $builderFactory
     */
    public function __construct(array $parsers, MetadataBuilderFactory $builderFactory)
    {
        $this->parsers = $parsers;
        $this->metadataBuilderFactory = $builderFactory;
    }

    /**
     * @inheritDoc
     */
    public function load(): array
    {
        $data = [];
        foreach ($this->parsers as $parser) {
            $parser->load($data);
        }
        $map = $this->metadataBuilderFactory->createMetadataBuilderList($data);
        $result = [];
        foreach ($map as $type => $metadataBuilder) {
            $result[$type] = $metadataBuilder->getMetadata($map);
        }
        return array_reverse($result);
    }
}
