<?php

namespace JsonApi\Normalizer;

/**
 * @package JsonApi\Normalizer
 */
interface TypeNormalizerInterface extends NormalizerInterface
{
    public function getType(): string;
}
