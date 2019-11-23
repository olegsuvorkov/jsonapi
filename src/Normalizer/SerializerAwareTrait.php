<?php

namespace JsonApi\Normalizer;

/**
 * @package JsonApi\Normalizer
 */
trait SerializerAwareTrait
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Sets the serializer.
     *
     * @param SerializerInterface $serializer A SerializerInterface instance
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
