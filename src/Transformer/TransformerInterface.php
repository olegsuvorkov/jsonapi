<?php

namespace JsonApi\Transformer;

/**
 * @package JsonApi\Transformer
 */
interface TransformerInterface
{
    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param mixed $data
     * @param array $options
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function transformScalar($data, array $options);

    /**
     * @param array &$ids
     * @param array $options
     * @return mixed
     */
    public function reverseTransformScalar(array &$ids, array $options);

    /**
     * @param mixed $data
     * @param array $options
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function transform($data, array $options);

    /**
     * @param mixed $data
     * @param array $options
     * @return mixed
     */
    public function reverseTransform($data, array $options);

    public function serializeOptions(array $options): array;
}
