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
     * @param RouteInterface $route
     */
    public function add(RouteInterface $route): void;

    /**
     * Adds a collection to the collection.
     *
     * @param RouteCollectionInterface $collection
     */
    public function addCollection(RouteCollectionInterface $collection): void;

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

    /**
     * Sets the route methods for the entire collection.
     *
     * @param string[]|string $methods List of request methods to allow.
     */
    public function setMethods($methods): void;

    /**
     * Adds a route prefix to the entire collection.
     *
     * @param string $prefix
     */
    public function addPrefix(string $prefix): void;

    /**
     * Adds a route name prefix to the entire collection.
     *
     * @param string $prefix
     */
    public function addNamePrefix(string $prefix): void;

    /**
     * Adds route constraints to the entire collection.
     *
     * @param string[] $constraints List of constraints for route variables.
     */
    public function addConstraints(array $constraints): void;

    /**
     * Adds route parameters to the entire collection.
     *
     * @param array<string|null> $params Parameters to pass to the route.
     */
    public function addParams(array $params): void;
}
