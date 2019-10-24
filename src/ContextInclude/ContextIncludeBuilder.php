<?php

namespace JsonApi\ContextInclude;

use JsonApi\Exception\ParseUrlException;
use JsonApi\Metadata\FieldInterface;
use JsonApi\Metadata\MetadataInterface;
use JsonApi\Metadata\RegisterInterface;
use JsonApi\Metadata\UndefinedMetadataException;

/**
 * @package JsonApi\ContextInclude
 */
class ContextIncludeBuilder
{
    /**
     * @var RegisterInterface
     */
    private $register;

    /**
     * @var string
     */
    private $type;

    public function __construct(RegisterInterface $register, string $type)
    {
        $this->register = $register;
        $this->type     = $type;
    }

    /**
     * @param string $data
     * @return ContextInclude
     * @throws ParseUrlException
     */
    public function build($data): ContextIncludeInterface
    {
        if (is_string($data)) {
            try {
                $metadata = $this->register->getByType($this->type);
                $include = new ContextInclude();
                foreach (explode(',', $data) as $field) {
                    $this->addContextInclude($metadata, $include, explode('.', $field));
                }
                return $include;
            } catch (UndefinedMetadataException $e) {
                throw new ParseUrlException($e->getMessage(), 0, $e);
            }
        }
        throw new ParseUrlException();
    }

    /**
     * @param MetadataInterface $metadata
     * @param ContextIncludeInterface $context
     * @param array $parts
     * @throws ParseUrlException
     */
    private function addContextInclude(MetadataInterface $metadata, ContextIncludeInterface $context, array $parts)
    {
        if ($part = array_shift($parts)) {
            $notExist = true;
            foreach ($metadata->findRelationships($part) as $field) {
                $this->addContextInclude(
                    $field->getTargetMetadata(),
                    $this->createContextInclude($context, $field),
                    $parts
                );
                $notExist = false;
            }
            if ($notExist) {
                throw new ParseUrlException(sprintf('not find property `%s` in `%s`', $part, $metadata->getType()));
            }
        }
    }

    private function createContextInclude(ContextIncludeInterface $context, FieldInterface $field)
    {
        if ($item = $context->findBy($field)) {
            return $item;
        } elseif ($field->getOption('multiple', false)) {
            return $context->add(new MultipleContextInclude($field));
        } else {
            return $context->add(new ContextInclude($field));
        }
    }
}
