<?php

namespace Bitty\Router;

interface RouteInterface
{
    /**
     * Sets the route methods.
     *
     * @param string[]|string $methods List of request methods to allow.
     *
     * @return RouteInterface
     */
    public function setMethods($methods): RouteInterface;

    /**
     * Gets the route methods.
     *
     * @return string[]
     */
    public function getMethods(): array;

    /**
     * Sets the route path.
     *
     * @param string $path Route path.
     *
     * @return RouteInterface
     */
    public function setPath(string $path): RouteInterface;

    /**
     * Gets the route path.
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Sets the route callback.
     *
     * @param callable|string $callback Callback to call.
     *
     * @return RouteInterface
     *
     * @throws \InvalidArgumentException
     */
    public function setCallback($callback): RouteInterface;

    /**
     * Gets the route callback.
     *
     * @return callable|string
     */
    public function getCallback();

    /**
     * Sets the route constraints.
     *
     * @param string[] $constraints List of constraints for route variables.
     *
     * @return RouteInterface
     */
    public function setConstraints(array $constraints): RouteInterface;

    /**
     * Gets the route constraints.
     *
     * This should return an array of patterns keyed by the parameter they fill.
     *
     * For example, if the path was /products/{id} then you'd probably want a
     * constraint that restricts 'id' to be an integer: ['id' => '\d+']
     *
     * This example assumes regex constraints are allowed. Each implementation
     * may apply constraints however they want and are not required to use regex.
     *
     * @return string[]
     */
    public function getConstraints(): array;

    /**
     * Adds to the route constraints.
     *
     * @param string[] $constraints List of constraints for route variables.
     *
     * @return RouteInterface
     */
    public function addConstraints(array $constraints): RouteInterface;

    /**
     * Gets a matchable pattern that combines that path and constraints.
     *
     * @return string
     */
    public function getPattern(): string;

    /**
     * Sets the route name.
     *
     * @param string|null $name Route name.
     *
     * @return RouteInterface
     */
    public function setName(?string $name): RouteInterface;

    /**
     * Gets the route name.
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Sets the route parameters.
     *
     * @param array<string|null> $params Parameters to pass to the route.
     *
     * @return RouteInterface
     */
    public function setParams(array $params): RouteInterface;

    /**
     * Gets the route parameters.
     *
     * @return array<string|null>
     */
    public function getParams(): array;

    /**
     * Adds to the route parameters.
     *
     * @param array<string|null> $params Parameters to pass to the route.
     *
     * @return RouteInterface
     */
    public function addParams(array $params): RouteInterface;
}
