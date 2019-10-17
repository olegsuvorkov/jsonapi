<?php

namespace JsonApi\Parser;

use JsonApi\Exception\LoaderException;
use Symfony\Component\Yaml\Yaml;

/**
 * @package JsonApi\Parser
 */
class YamlParser implements ParserInterface
{
    /**
     * @var string
     */
    private $file;

    /**
     * @param string $file
     */
    public function __construct(string $file)
    {
        $this->file = $file;
    }

    /**
     * @inheritDoc
     */
    public function load(array &$data): void
    {
        $parseData = Yaml::parseFile($this->file, Yaml::PARSE_CONSTANT);
        if (is_array($parseData)) {
            foreach ($parseData as $class => $parameters) {
                if (!is_string($class)) {
                    throw new LoaderException();
                }
                if (!is_array($parameters)) {
                    throw new LoaderException();
                }
                $parameters['file'] = $this->file;
                $data[ltrim($class, '\\')] = $parameters;
            }
        }
    }
}
