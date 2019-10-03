<?php

namespace JsonApi\Context;

use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @package JsonApi
 */
interface ContextInterface
{
    /**
     * @return ClassMetadata
     */
    public function getClassMetadata(): ClassMetadata;

    /**
     * @param array $paths
     * @return string
     */
    public function getField(array $paths): string;

    /**
     * @return array
     */
    public function getInclude(): array;

    /**
     * @return string[][]
     */
    public function getFields(): array;
}
