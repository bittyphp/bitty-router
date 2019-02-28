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
    public function addCollection(RouteCollectionInterface $collection): void
    {
        foreach ($collection->all() as $route) {
            $this->add($route);
        }
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

    /**
     * {@inheritDoc}
     */
    public function setMethods($methods): void
    {
        foreach ($this->routes as $route) {
            $route->setMethods($methods);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function addPrefix(string $prefix): void
    {
        foreach ($this->routes as $route) {
            $route->setPath($prefix.$route->getPath());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function addNamePrefix(string $prefix): void
    {
        $routes = [];

        foreach ($this->routes as $name => $route) {
            $routes[$prefix.$name] = $route;
            $name = $route->getName();
            if ($name === null) {
                continue;
            }

            $route->setName($prefix.$name);
        }

        $this->routes = $routes;
    }

    /**
     * {@inheritDoc}
     */
    public function addConstraints(array $constraints): void
    {
        foreach ($this->routes as $route) {
            $route->addConstraints($constraints);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function addParams(array $params): void
    {
        foreach ($this->routes as $route) {
            $route->addParams($params);
        }
    }
}
