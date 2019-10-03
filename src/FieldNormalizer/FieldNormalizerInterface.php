<?php

namespace JsonApi\FieldNormalizer;

use JsonApi\Exception\LoaderException;
use JsonApi\Metadata\FieldInterface;

/**
 * @package JsonApi\FieldNormalizer
 */
interface FieldNormalizerInterface
{
    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param FieldInterface $field
     * @param $data
     */
    public function normalize(FieldInterface $field, $data);

    /**
     * @param FieldInterface $field
     * @param array $data
     */
    public function denormalize(FieldInterface $field, $data);
//
//    /**
//     * @param FieldInterface $field
//     * @param array $parameters
//     * @return void
//     * @throws LoaderException
//     */
//    public function configure(FieldInterface $field, array &$parameters): void;
}
