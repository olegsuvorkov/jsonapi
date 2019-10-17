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
}
