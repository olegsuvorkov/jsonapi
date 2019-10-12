<?php

namespace JsonApi\Transformer;

/**
 * @package JsonApi\Transformer
 */
class TransformerPool
{
    /**
     * @var TransformerInterface[]
     */
    private $types = [];

    /**
     * @param string $type
     * @return TransformerInterface
     * @throws UndefinedTransformerException
     */
    public function get(string $type)
    {
        if (isset($this->types[$type])) {
            return $this->types[$type];
        }
        throw new UndefinedTransformerException(sprintf('Undefined type `%s`', $type));
    }

    /**
     * @param TransformerInterface $transformer
     */
    public function add(TransformerInterface $transformer): void
    {
        $this->types[$transformer->getType()] = $transformer;
    }
}
