<?php

namespace Bitty\Router;

use Bitty\Router\Exception\NotFoundException;
use Bitty\Router\Route;
use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteInterface;

class RouteCollection implements RouteCollectionInterface
{
    /**
     * @var RouteInterface[]
     */
    protected $routes = [];

    /**
     * Route counter.
     *
     * @var int
     */
    protected $routeCounter = 0;

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
    public function add(
        $methods,
        string $path,
        $callback,
        array $constraints = [],
        string $name = null
    ): RouteInterface {
        $route = new Route(
            $methods,
            $path,
            $callback,
            $constraints,
            $name,
            $this->routeCounter++
        );

        if (null === $name) {
            $name = $route->getIdentifier();
        }

        $this->routes[$name] = $route;

        return $route;
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
        if (isset($this->routes[$name])) {
            unset($this->routes[$name]);
        }
    }
}
