<?php

namespace JsonApi\Transformer;

/**
 * @package JsonApi\Transformer
 */
interface TransformerPoolInterface
{
    /**
     * @param string $type
     * @return TransformerInterface
     * @throws UndefinedTransformerException
     */
    public function getTransformer(string $type): TransformerInterface;
}
