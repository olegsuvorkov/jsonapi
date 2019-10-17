<?php

namespace JsonApi\ContextInclude;

/**
 * @package JsonApi\ContextInclude
 */
class IncludeStack implements IncludeStackInterface
{
    private $list;

    private $snapshot;

    /**
     * @param array $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
        $this->snapshot = $list;
    }

    /**
     * @param $object
     */
    public function add($object): void
    {
        if (!in_array($object, $this->list, true)) {
            $this->list[] = $object;
        }
    }

    public function all()
    {
        foreach ($this->list as $item) {
            if (!in_array($item, $this->snapshot, true)) {
                yield $item;
            }
        }
    }
}