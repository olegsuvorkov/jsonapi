<?php

namespace JsonApi\ContextInclude;

/**
 * @package JsonApi\ContextInclude
 */
class MultipleContextInclude extends ContextInclude
{
    protected function addToStack($data, IncludeStackInterface $stack)
    {
        $metadata = $this->field->getOption('target');
        foreach ($this->field->getValue($data) as $item) {
            $stack->add($item);
            $this->register($metadata, $item, $stack);
        }
    }
}