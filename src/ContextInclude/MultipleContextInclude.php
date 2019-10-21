<?php

namespace JsonApi\ContextInclude;

/**
 * @package JsonApi\ContextInclude
 */
class MultipleContextInclude extends ContextInclude
{
    protected function addToStack($data, array &$stack)
    {
        $metadata = $this->field->getOption('target');
        foreach ($this->field->getValue($data) as $item) {
            if (!in_array($item, $stack, true)) {
                $stack[] = $item;
            }
            $this->register($metadata, $item, $stack);
        }
    }
}