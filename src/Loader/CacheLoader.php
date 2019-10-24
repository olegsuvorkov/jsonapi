<?php

namespace JsonApi\Loader;

use JsonApi\Exception\LoaderException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AdapterInterface;

/**
 * @package JsonApi\Loader
 */
class CacheLoader implements LoaderInterface
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var string|null
     */
    private $cacheKey;

    /**
     * @param string|null $cacheKey
     * @param AdapterInterface $adapter
     * @param LoaderInterface $loader
     */
    public function __construct(?string $cacheKey, AdapterInterface $adapter, LoaderInterface $loader)
    {
        $this->adapter  = $adapter;
        $this->loader   = $loader;
        $this->cacheKey = $cacheKey;
    }

    /**
     * @inheritDoc
     */
    public function load(): array
    {
        if ($this->cacheKey === null) {
            return $this->loader->load();
        }
        try {
            $item = $this->adapter->getItem($this->cacheKey);
            if ($item->isHit()) {
                $data = $item->get();
                if (is_array($data)) {
                    return $data;
                }
            }
            $data = $this->loader->load();
            $item->set($data);
            $this->adapter->save($item);
            return $data;
        } catch (InvalidArgumentException $e) {
        }
        throw new LoaderException($e->getMessage(), $e->getCode(), $e);
    }
}
