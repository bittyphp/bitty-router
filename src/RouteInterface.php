<?php

namespace Bitty\Router;

interface RouteInterface
{
    /**
     * Gets the route identifier.
     *
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * Gets the route methods.
     *
     * @return string[]
     */
    public function getMethods(): array;

    /**
     * Gets the route path.
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Gets the route callback.
     *
     * @return callable|string
     */
    public function getCallback();

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
     * Gets a matchable pattern that combines that path and constraints.
     *
     * @return string
     */
    public function getPattern(): string;

    /**
     * Gets the route name.
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Gets the route parameters.
     *
     * @return string[]
     */
    public function getParams(): array;
}
