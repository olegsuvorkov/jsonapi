<?php

namespace JsonApi\Context;

use JsonApi\Exception\InvalidTypeException;
use JsonApi\Exception\ParseUrlException;
use JsonApi\Metadata\RegisterInterface;
use JsonApi\Metadata\UndefinedMetadataException;

/**
 * @package JsonApi
 */
class ContextFactory implements ContextFactoryInterface
{
    /**
     * @var RegisterInterface
     */
    private $register;

    /**
     * @param RegisterInterface $register
     */
    public function __construct(RegisterInterface $register)
    {
        $this->register = $register;
    }

    /**
     * @inheritDoc
     */
    public function createContext($type, $include, $fields): ContextInterface
    {
        if (is_string($type)) {
            try {
                return new Context($type, $this->normalizeFields($fields), $this->register);
            } catch (UndefinedMetadataException $e) {
                throw new InvalidTypeException(sprintf('Undefined type `%s`', $type), 0, $e);
            }
        }
        throw InvalidTypeException::invalidType();
    }
}
