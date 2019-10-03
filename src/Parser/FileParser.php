<?php

namespace JsonApi\Parser;

use JsonApi\Exception\LoaderException;
use JsonApi\Metadata\Metadata;
use JsonApi\Metadata\MetadataInterface;
use JsonApi\Metadata\RegisterInterface;
use JsonApi\Metadata\UndefinedMetadataException;
use Symfony\Component\Yaml\Yaml;

/**
 * @package JsonApi\Parser
 */
class FileParser implements FileParserInterface
{
    /**
     * @var MetadataParserInterface
     */
    private $metadataParser;

    /**
     * @var string
     */
    private $file;

    /**
     * @var array
     */
    private $data = [];
    /**
     * @var RegisterInterface
     */
    private $register;

    /**
     * @param string $file
     * @param MetadataParserInterface $metadataParser
     * @param RegisterInterface $register
     */
    public function __construct(string $file, MetadataParserInterface $metadataParser, RegisterInterface $register)
    {
        $this->metadataParser = $metadataParser;
        $this->file = $file;
        $this->register = $register;
    }

    /**
     * @inheritDoc
     */
    public function load(): void
    {
        $data = Yaml::parseFile($this->file, Yaml::PARSE_CONSTANT);
        foreach ($data as $class => $parameters) {
            if ($parameters === null) {
                $parameters = [];
            }
            if (is_array($parameters)) {
                $metadata = new Metadata(ltrim($class, '\\'));
                $metadata->setType($this->normalizeType($parameters));
                $this->register->add($metadata);
                $this->data[$metadata->getClass()] = $parameters;
            } else {
                throw new LoaderException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function normalize(): void
    {
        foreach ($this->data as $class => &$parameters) {
            if ($map = $this->normalizeDiscriminationMap($parameters)) {
                $metadata = $this->register->getByClass($class);
                $discriminationAttribute = $this->normalizeDiscriminationAttribute($parameters);
                $metadata->setDiscriminatorAttribute($discriminationAttribute);
                if (!isset($parameters['attributes'])) {
                    $parameters['attributes'] = [];
                }
                foreach ($map as $value => $targetMetadata) {
                    $metadata->addDiscriminator($value, $targetMetadata);
                }
            }
            unset($parameters);
        }
    }

    /**
     * @inheritDoc
     */
    public function parse(): void
    {
        foreach ($this->data as $class => $parameters) {
            $this->metadataParser->parseMetadata($this->register->getByClass($class), $parameters);
        }
    }

    /**
     * @param array $parameters
     * @return string|null
     * @throws LoaderException
     */
    private function normalizeType(array &$parameters): ?string
    {
        $type = null;
        if (array_key_exists('type', $parameters)) {
            $type = $parameters['type'];
            unset($parameters['type']);
            if (!is_string($type) || false === preg_match('~^[a-zA-Z0-9_]$~', $type)) {
                throw new LoaderException();
            }
        }
        return $type;
    }

    /**
     * @param array $parameters
     * @return MetadataInterface[]
     * @throws LoaderException
     * @throws UndefinedMetadataException
     */
    private function normalizeDiscriminationMap(array &$parameters): array
    {
        $list = [];
        if (array_key_exists('discrimination_map', $parameters)) {
            $map = $parameters['discrimination_map'];
            unset($parameters['discrimination_map']);
            if (!is_array($map)) {
                throw new LoaderException();
            }
            if (!$map) {
                throw new LoaderException();
            }
            foreach ($map as $value => $class) {
                if (is_string($class)) {
                    $list[$value] = $this->register->getByClass($class);
                } else {
                    throw new LoaderException();
                }
            }
        }
        return $list;
    }

    /**
     * @param array $parameters
     * @return string
     * @throws LoaderException
     */
    private function normalizeDiscriminationAttribute(array &$parameters): string
    {
        $attribute = $parameters['discrimination_attribute'] ?? $this;
        unset($parameters['discrimination_attribute']);
        if (is_string($attribute)) {
            return $attribute;
        } elseif ($attribute === $this) {
            throw new LoaderException();
        } else {
            throw new LoaderException();
        }
    }
}
