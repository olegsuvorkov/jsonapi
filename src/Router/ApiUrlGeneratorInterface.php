<?php

namespace JsonApi\Router;

/**
 * @package JsonApi\Router
 */
interface ApiUrlGeneratorInterface
{
    /**
     * @param string $type
     * @return string
     */
    public function generateUrl(string $type): string;

    /**
     * @param string $type
     * @param string $id
     * @return string
     */
    public function generateEntityUrl(string $type, string $id): string;

    /**
     * @param string $type
     * @param string $id
     * @param string $name
     * @return string
     */
    public function generateRelationshipUrl(string $type, string $id, string $name): string;
}
