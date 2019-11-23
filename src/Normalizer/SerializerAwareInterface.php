<?php

namespace JsonApi\Normalizer;

/**
 * @package JsonApi\Normalizer
 */
interface SerializerAwareInterface
{
    /**
     * @param SerializerInterface $serializer
     */
    public function setSerializer(SerializerInterface $serializer): void;
}
