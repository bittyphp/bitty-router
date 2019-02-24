<?php

namespace Bitty\Router;

use Bitty\Router\Exception\NotFoundException;
use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteInterface;

class RouteCollection implements RouteCollectionInterface
{
    /**
     * @var RouteInterface[]
     */
    private $routes = [];

    /**
     * Route counter.
     *
     * @var int
     */
    private $routeCounter = 0;

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * {@inheritDoc}
     */
    public function add(RouteInterface $route): void
    {
        $name = $route->getName();

        if (empty($name)) {
            $name = '_route_'.$this->routeCounter;
        }

        $this->routes[$name] = $route;
        $this->routeCounter++;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $name): bool
    {
        return isset($this->routes[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $name): RouteInterface
    {
        if (!isset($this->routes[$name])) {
            throw new NotFoundException(sprintf('No route named "%s" exists.', $name));
        }

        return $this->routes[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function remove(string $name): void
    {
        if (!isset($this->routes[$name])) {
            return;
        }

        unset($this->routes[$name]);
    }
}
