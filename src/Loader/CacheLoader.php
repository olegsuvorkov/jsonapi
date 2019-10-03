<?php

namespace JsonApi\Loader;

use JsonApi\Exception\LoaderException;
use JsonApi\Metadata\MetadataInterface;
use JsonApi\Metadata\RegisterInterface;
use Psr\Cache\CacheItemInterface;
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
    public function load(RegisterInterface $register): void
    {
        if ($this->cacheKey === null) {
            $this->loader->load($register);
        } else {
            $metadataList = null;
            try {
                $item = $this->adapter->getItem($this->cacheKey);
                $metadataList = $this->getList($item);
            } catch (InvalidArgumentException $e) {
                throw new LoaderException($e->getMessage(), $e->getCode(), $e);
            }
            if ($metadataList !== null) {
                foreach ($metadataList as $metadata) {
                    $register->add($metadata);
                }
            } else {
                $this->loader->load($register);
                $item->set(array_values($register->all()));
                $this->adapter->save($item);
            }
        }
    }

    /**
     * @param CacheItemInterface $item
     * @return MetadataInterface[]|null
     * @throws LoaderException
     */
    private function getList(CacheItemInterface $item): ?array
    {
        $list = null;
        if ($item->isHit()) {
            $data = $item->get();
            if (is_array($data)) {
                $list = [];
                foreach ($data as $metadata) {
                    if ($metadata instanceof MetadataInterface) {
                        $list[] = $metadata;
                    } else {
                        throw new LoaderException();
                    }
                }
            }
        }
        return $list;
    }
}
