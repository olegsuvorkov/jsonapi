<?php

namespace JsonApi\Context;

use Doctrine\ORM\Mapping\ClassMetadata;
use JsonApi\Metadata\Register;

/**
 * @package JsonApi
 */
class Context implements ContextInterface
{
    /**
     * @var string[][]
     */
    public $fields = [];

    /**
     * @var string[]
     */
    public $include = [];

    /**
     * @var int
     */
    private $alias = 0;
    /**
     * @var Register
     */
    private $register;

    /**
     * @param ClassMetadata $metadata
     * @param Register $register
     */
    public function __construct(ClassMetadata $metadata, Register $register)
    {
        $this->include = [$metadata, [], true, 't'.$this->alias++];
        $this->register = $register;
    }

    /**
     * @return ClassMetadata
     */
    public function getClassMetadata(): ClassMetadata
    {
        return $this->include[0];
    }

    public function addPath(array $paths, $include)
    {
        $map = &$this->include;
        foreach ($paths as [$metadata, $association]) {
            $map = &$map[1][$association];
            if (!$map) {
                $map = [$metadata, [], $include, 't'.$this->alias++];
            }
            if ($include && !$map[2]) {
                $map[2] = $include;
            }
        }
    }

    public function getField(array $paths): string
    {
        $index = 0;
        $paths = $this->register->getPath($this->getClassMetadata(), $paths, $index);
        $field = $paths[$index] ?? null;

        $map = &$this->include;
        $length = count($paths);
        if ($field === null) {
            $length--;
            $field = $paths[$length][1];
        }
        for ($i = 0; $i < $length; $i++) {
            [$metadata, $association] = $paths[$i];
            $map = &$map[1][$association];
            if (!$map) {
                $map = [$metadata, [], false, 't'.$this->alias++];
            }
        }
        return $map[3].'.'.$field;
    }

    /**
     * @return array
     */
    public function getInclude(): array
    {
        return $this->include;
    }

    /**
     * @return string[][]
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
