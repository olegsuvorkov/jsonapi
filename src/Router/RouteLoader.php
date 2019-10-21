<?php

namespace JsonApi\Router;

use JsonApi\Controller\ControllerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @package JsonApi\Router
 */
class RouteLoader
{
    /**
     * @var ControllerInterface[]
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
     * @param string $path
     * @param string $name
     * @param ControllerInterface[] $controllerList
     */
    public function __construct(string $path, string $name, array $controllerList)
    {
        $this->path = $path;
        $this->name = $name;
        $this->controllerList = $controllerList;
    }

    /**
     * @inheritDoc
     */
    public function loadRoutes(): RouteCollection
    {
        $collection = new RouteCollection();
        foreach ($this->controllerList as $controller) {
            $type   = $controller->getType();
            $name   = $this->name.$type.'_';
            $prefix = $this->path.$type;
            $class  = get_class($controller);
            $collection->add($name.'list', new Route($prefix, [
                '_controller' => $class.'::list',
                'type'        => $type,
            ], [], [], null, [], ['GET']));
            $collection->add($name.'get', new Route($prefix.'/{id}', [
                '_controller' => $class.'::fetch',
                'type'        => $type,
            ], [
                'id' => '[^/]+'
            ], [], null, [], ['GET']));
            $collection->add($name.'relationships', new Route($prefix.'/{id}/relationships/{relationship}', [
                '_controller' => $class.'::relationships',
                'type'        => $type,
            ], [
                'id' => '[^/]+',
                'relationship' => '[^/]+',
            ], [], null, [], ['GET']));
            $collection->add($name.'create', new Route($prefix, [
                '_controller' => $class.'::create',
                'type'        => $type,
            ], [
                'id' => '[^/]+',
            ], [], null, [], ['POST']));
        }
        return $collection;
    }
}
