<?php

namespace Bitty\Router;

use Bitty\Router\Exception\NotFoundException;
use Bitty\Router\RouteInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{
    /**
     * Adds a new route.
     *
     * @param string[]|string $methods
     * @param string $path
     * @param callable|string $callback
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
        string $name = null
    ): RouteInterface;

    /**
     * Checks if a route exists.
     *
     * @param string $name Name of the route.
     *
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Gets a route.
     *
     * @param string $name Name of the route.
     *
     * @return RouteInterface
     *
     * @throws NotFoundException When route does not exist.
     */
    public function get(string $name): RouteInterface;

    /**
     * Finds a route for the given request.
     *
     * @param ServerRequestInterface $request
     *
     * @return RouteInterface
     *
     * @throws NotFoundException When unable to find a route.
     */
    public function find(ServerRequestInterface $request): RouteInterface;

    /**
     * Generates a URI for a named route.
     *
     * @param string $name Name of the route.
     * @param mixed[] $params Key/value array of parameters to use.
     * @param string $type Type of URI to generate.
     *
     * @return string
     *
     * @throws NotFoundException When unable to find route.
     */
    public function generateUri(
        string $name,
        array $params = [],
        string $type = UriGeneratorInterface::ABSOLUTE_PATH
    ): string;
}
