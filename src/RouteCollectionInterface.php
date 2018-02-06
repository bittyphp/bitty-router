<?php

namespace Bitty\Router;

use Bitty\Router\Exception\NotFoundException;
use Bitty\Router\RouteInterface;

interface RouteCollectionInterface
{
    /**
     * Returns all the routes.
     *
     * @return RouteInterface[]
     */
    public function all();

    /**
     * Adds a new route.
     *
     * @param string[]|string $methods
     * @param string $path
     * @param \Closure|string $callback
     * @param string[] $constraints
     * @param string|null $name
     */
    public function add(
        $methods,
        $path,
        $callback,
        array $constraints = [],
        $name = null
    );

    /**
     * Checks if a route exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name);

    /**
     * Gets a route by name.
     *
     * @param string $name
     *
     * @return mixed
     *
     * @throws NotFoundException
     */
    public function get($name);

    /**
     * Removes a route.
     *
     * @param string $name
     */
    public function remove($name);
}
