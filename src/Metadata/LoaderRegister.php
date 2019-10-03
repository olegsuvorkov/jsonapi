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
    public function add(MetadataInterface $metadata): void
    {
        $this->getOriginal()->add($metadata);
    }

    /**
     * @inheritDoc
     */
    public function getByClass(string $class): MetadataInterface
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
    public function all(): array
    {
        return $this->getOriginal()->all();
    }

    private function getOriginal(): RegisterInterface
    {
        if ($this->original === null) {
            $register = new Register();
            $this->loader->load($register);
            $this->original = $register;
        }
        return $this->original;
    }
}
