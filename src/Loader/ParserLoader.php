<?php

namespace JsonApi\Loader;

use JsonApi\Exception\LoaderException;
use JsonApi\MetadataBuilder\BuilderException;
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
        try {
            $data = [];
            foreach ($this->parsers as $parser) {
                $parser->load($data);
            }
            return $this->metadataBuilderFactory->createMetadataList($data);
        } catch (BuilderException $e) {
            throw new LoaderException($e->getMessage(), 0, $e);
        }
    }
}
