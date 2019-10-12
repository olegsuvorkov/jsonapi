<?php


namespace JsonApi\Metadata;

use JsonApi\Exception\ParseUrlException;

/**
 * @package JsonApi\Metadata
 */
class ContextRegisterFactory
{
    /**
     * @var RegisterInterface
     */
    private $register;

    /**
     * @param RegisterInterface $register
     */
    public function __construct(RegisterInterface $register)
    {
        $this->register = $register;
    }

    /**
     * @param $fieldsMap
     * @return ContextRegister
     * @throws ParseUrlException
     */
    public function createContextRegister($fieldsMap): ContextRegister
    {
        if ($fieldsMap === null) {
            $fieldsMap = [];
        }
        if (is_array($fieldsMap)) {
            foreach ($fieldsMap as $type => &$fields) {
                if (is_string($fields)) {
                    $fields = explode(',', $fields);
                } else {
                    throw new ParseUrlException();
                }
                unset($fields);
            }
            return new ContextRegister($fieldsMap, $this->register);
        }
        throw new ParseUrlException();
    }
}
