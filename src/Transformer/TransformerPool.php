<?php

namespace JsonApi\Transformer;

/**
 * @package JsonApi\Transformer
 */
final class TransformerPool implements TransformerPoolInterface
{
    /**
     * @var TransformerInterface[]
     */
    private $transformers = [];

    /**
     * @param TransformerInterface[] $transformers
     */
    public function __construct(array $transformers)
    {
        foreach ($transformers as $transformer) {
            $this->transformers[$transformer->getType()] = $transformer;
        }
    }

    /**
     * @inheritDoc
     */
    public function getTransformer(string $type): TransformerInterface
    {
        $transformer = $this->transformers[$type] ?? null;
        if ($transformer) {
            return $transformer;
        }
        throw new UndefinedTransformerException(sprintf('Undefined type `%s`', $type));
    }
}
