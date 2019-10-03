<?php


namespace JsonApi\CriteriaFactory\PrefixParser;


use JsonApi\Exception\ParseUrlException;

abstract class AbstractPrefixParser implements PrefixParserInterface
{
    /**
     * @param string $data
     * @param string $token
     * @param int $index
     * @return string
     * @throws ParseUrlException
     */
    protected function match(string $data, string $token, int &$index): string
    {
        $position = strpos($data, $token, $index);
        if ($position !== false) {
            $value = substr($data, $index, $position - $index);
            $index = $position + 1;
            return $value;
        }
        throw new ParseUrlException();
    }

}