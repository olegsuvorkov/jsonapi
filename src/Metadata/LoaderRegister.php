<?php


namespace JsonApi\Metadata;


use JsonApi\Loader\LoaderInterface;

class LoaderRegister implements RegisterInterface
{
    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var RegisterInterface|null
     */
    private $original = null;

    /**
     * @param LoaderInterface $loader
     */
    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
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

    private function getOriginal(): RegisterInterface
    {
        if ($this->original === null) {
            $this->original = new Register($this->loader->load());
        }
        return $this->original;
    }
}
