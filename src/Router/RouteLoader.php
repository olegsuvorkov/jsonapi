<?php

namespace JsonApi\Router;

use JsonApi\Controller\ControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @package JsonApi\Router
 */
class RouteLoader
{
    const BASE_PATH          = '';
    const ENTITY_PATH        = '/{id}';
    const RELATIONSHIPS_PATH = '/{id}/relationships/{relationship}';

    /**
     * @var array
     */
    private $options = [
        'expose' => true,
    ];

    /**
     * @var array[][]
     */
    private static $routeParameters = [
        'list'                 => ['list',                self::BASE_PATH,          Request::METHOD_GET, [
        ]],
        'create'               => ['create',              self::BASE_PATH,          Request::METHOD_POST, [
        ]],
        'get'                  => ['fetch',               self::ENTITY_PATH,        Request::METHOD_GET, [
            'id'           => '[^/]+',
        ]],
        'patch'                => ['patch',               self::ENTITY_PATH,        Request::METHOD_PATCH, [
            'id'           => '[^/]+',
        ]],
        'delete'               => ['delete',              self::ENTITY_PATH,        Request::METHOD_DELETE, [
            'id'           => '[^/]+',
        ]],
        'relationships'        => ['relationships',       self::RELATIONSHIPS_PATH, Request::METHOD_GET, [
            'id'           => '[^/]+',
            'relationship' => '[^/]+',
        ]],
        'relationships_delete' => ['relationshipsDelete', self::RELATIONSHIPS_PATH, Request::METHOD_DELETE, [
            'id'           => '[^/]+',
            'relationship' => '[^/]+',
        ]],
        'relationships_patch'  => ['relationshipsPatch',  self::RELATIONSHIPS_PATH, Request::METHOD_PATCH, [
            'id'           => '[^/]+',
            'relationship' => '[^/]+',
        ]],
        'relationships_post'   => ['relationshipsPost',   self::RELATIONSHIPS_PATH, Request::METHOD_POST, [
            'id'           => '[^/]+',
            'relationship' => '[^/]+',
        ]],
    ];

    /**
     * @var string[]
     */
    private $controllerList;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $schemas;
    /**
     * @var string|null
     */
    private $host;

    /**
     * @param string[] $schemas
     * @param string $host
     * @param string $path
     * @param string $name
     * @param ControllerInterface[] $controllerList
     */
    public function __construct(array $schemas, ?string $host, string $path, string $name, array $controllerList)
    {
        $this->schemas = $schemas;
        $this->host = $host;
        $this->path = $path;
        $this->name = $name;
        foreach ($controllerList as $serviceId => $controller) {
            $this->controllerList[$controller->getType()] = $serviceId;
        }
    }

    /**
     * @inheritDoc
     */
    public function loadRoutes(): RouteCollection
    {
        $collection = new RouteCollection();
        foreach ($this->controllerList as $type => $controller) {
            foreach (self::$routeParameters as $name => [$action, $path, $method, $requirements]) {
                $collection->add(
                    $this->name.$type.'_'.$name,
                    new Route(
                        $this->path.$type.$path,
                        ['_controller' => $controller.'::'.$action, 'type' => $type],
                        $requirements,
                        $this->options,
                        $this->host,
                        $this->schemas,
                        [$method]
                    ));
            }
        }
        return $collection;
    }
}
