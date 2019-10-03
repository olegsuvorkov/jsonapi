<?php

namespace JsonApi\Context;

use JsonApi\Exception\ParseUrlException;
use JsonApi\Exception\InvalidTypeException;
use JsonApi\Metadata\Register;

/**
 * @package JsonApi
 */
class ContextFactory implements ContextFactoryInterface
{
    /**
     * @var Register
     */
    private $register;

    /**
     * @param Register $register
     */
    public function __construct(Register $register)
    {
        $this->register = $register;
    }

    /**
     * @inheritDoc
     */
    public function createContext($type, $include, $fields): ContextInterface
    {
        if (is_string($type)) {
            $metadata = $this->register->getByType($type);
            $context  = new Context($metadata, $this->register);
            $this->parseInclude($context, $include);
            $this->parseFieldsMap($context, $fields);
            return $context;
        }
        throw InvalidTypeException::invalidType();
    }

    /**
     * @param Context $context
     * @param $includes
     * @throws ParseUrlException
     */
    private function parseInclude(Context $context, $includes)
    {
        if ($includes && is_string($includes)) {
            foreach (explode(',', $includes) as $include) {
                $fields = explode('.', $include);
                $index = 0;
                $paths = $this->register->getPath($context->getClassMetadata(), $fields, $index);
                if ($index === 0) {
                    $context->addPath($paths, true);
                } else {
                    throw new ParseUrlException();
                }
            }
        } else {
            throw ParseUrlException::invalidQueryParameter('include');
        }
    }

    /**
     * @param Context $context
     * @param $relTypeToFields
     * @throws ParseUrlException
     * @throws InvalidTypeException
     */
    private function parseFieldsMap(Context $context, $relTypeToFields)
    {
        if (is_array($relTypeToFields)) {
            foreach ($relTypeToFields as $type => $fields) {
                if (is_string($type)) {
                    $context->fields[$type] = $this->parseFields($type, $fields);
                } else {
                    throw InvalidTypeException::invalidType();
                }
            }
        } else {
            throw ParseUrlException::invalidQueryParameter('fields');
        }
    }

    /**
     * @param string $type
     * @param $fields
     * @return string[]
     * @throws ParseUrlException
     * @throws InvalidTypeException
     */
    private function parseFields(string $type, $fields)
    {
        if (is_string($fields)) {
            $metadata = $this->register->getMetadataByType($type);
            $list = [];
            foreach (explode(',', $fields) as $item) {
                if ($metadata->hasField($item) || $metadata->hasAssociation($item)) {
                    $list[] = $item;
                } else {
                    throw ParseUrlException::invalidTypeField($type, $item);
                }
            }
        } else {
            throw ParseUrlException::invalidQueryFieldType('fields', $type);
        }
        return $list;
    }
}
