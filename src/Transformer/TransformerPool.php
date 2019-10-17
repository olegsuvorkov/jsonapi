<?php

namespace JsonApi\Transformer;

/**
 * @package JsonApi\Transformer
 */
final class TransformerPool
{
    /**
     * @var TransformerInterface[]
     */
    private static $_types = [];

    private function __construct()
    {
    }

    /**
     * @param string $type
     * @return TransformerInterface
     * @throws UndefinedTransformerException
     */
    public static function get(string $type)
    {
        $transformer = self::$_types[$type] ?? null;
        if ($transformer) {
            return $transformer;
        }
        throw new UndefinedTransformerException(sprintf('Undefined type `%s`', $type));
    }

    public static function add(TransformerInterface $transformer)
    {
        self::$_types[$transformer->getType()] = $transformer;
    }
}
