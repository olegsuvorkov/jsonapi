<?php

namespace JsonApi\Loader;

use JsonApi\Exception\LoaderException;
use JsonApi\Metadata\RegisterInterface;
use JsonApi\Metadata\UndefinedMetadataException;
use JsonApi\Parser\FileParser;
use JsonApi\Parser\FileParserInterface;
use JsonApi\Parser\MetadataParserFactory;
use Symfony\Component\Config\FileLocatorInterface;

/**
 * @package JsonApi\Loader
 */
class FileLoader implements LoaderInterface
{
    /**
     * @var array
     */
    private $files;

    /**
     * @var MetadataParserFactory
     */
    private $metadataParserFactory;
    /**
     * @var FileLocatorInterface
     */
    private $locator;

    /**
     * @param array $files
     * @param FileLocatorInterface $locator
     * @param MetadataParserFactory $metadataParserFactory
     */
    public function __construct(array $files, FileLocatorInterface $locator, MetadataParserFactory $metadataParserFactory)
    {
        $this->files = $files;
        $this->metadataParserFactory = $metadataParserFactory;
        $this->locator = $locator;
    }

    /**
     * @param RegisterInterface $register
     * @throws LoaderException
     * @throws UndefinedMetadataException
     */
    public function load(RegisterInterface $register): void
    {
        /** @var FileParserInterface[] $parserList */
        $parserList = [];

        $metadataParser = $this->metadataParserFactory->createMetadataParser($register);
        foreach ($this->files as $file) {
            $file = $this->locator->locate($file);
            $parserList[] = new FileParser($file, $metadataParser, $register);
        }
        foreach ($parserList as $parser) {
            $parser->load();
        }
        foreach ($parserList as $parser) {
            $parser->normalize();
        }
        foreach ($parserList as $parser) {
            $parser->parse();
        }
    }
}
