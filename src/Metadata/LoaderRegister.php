<?php

namespace JsonApi\Metadata;

/**
 * @package JsonApi\Metadata
 */
class LoaderRegister implements RegisterInterface
{
    /**
     * @var RegisterInterface|null
     */
    private $original = null;

    /**
     * @var RegisterFactoryInterface
     */
    private $registerFactory;

    /**
     * @param RegisterFactoryInterface $registerFactory
     */
    public function __construct(RegisterFactoryInterface $registerFactory)
    {
        $this->registerFactory = $registerFactory;
    }

    /**
     * @inheritDoc
     */
    public function getByClass($class): MetadataInterface
    {
        return $this->getOriginal()->getByClass($class);
    }

    /**
     * @inheritDoc
     */
    public function getByType(string $type): MetadataInterface
    {
        return $this->getOriginal()->getByType($type);
    }

    /**
     * @inheritDoc
     */
    public function hasType(string $type): bool
    {
        return $this->getOriginal()->hasType($type);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->getOriginal()->jsonSerialize();
    }

    private function getOriginal(): RegisterInterface
    {
        if ($this->original === null) {
            $this->original = $this->registerFactory->createRegister();
        }
        return $this->original;
    }
}
