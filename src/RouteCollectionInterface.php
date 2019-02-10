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
    public function all(): array;

    /**
     * Adds a new route.
     *
     * @param string[]|string $methods
     * @param string $path
     * @param callable|string|mixed $callback
     * @param string[] $constraints
     * @param string|null $name
     *
     * @return RouteInterface
     */
    public function add(
        $methods,
        string $path,
        $callback,
        array $constraints = [],
        ?string $name = null
    ): RouteInterface;

    /**
     * Checks if a route exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Gets a route by name.
     *
     * @param string $name
     *
     * @return RouteInterface
     *
     * @throws NotFoundException
     */
    public function get(string $name): RouteInterface;

    /**
     * Removes a route.
     *
     * @param string $name
     */
    public function remove(string $name): void;
}
