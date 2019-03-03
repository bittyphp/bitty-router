<?php

namespace Bitty\Router;

use Bitty\Router\RouteCompiler;
use Bitty\Router\RouteInterface;

class Route implements RouteInterface
{
    /**
     * List of allowed request methods, e.g. GET, POST, etc.
     *
     * @var string[]
     */
    private $methods = [];

    /**
     * Route path.
     *
     * @var string
     */
    private $path = null;

    /**
     * Route callback.
     *
     * @var callable|string
     */
    private $callback = null;

    /**
     * List of constraints for route variables.
     *
     * @var string[]
     */
    private $constraints = [];

    /**
     * Route name.
     *
     * @var string|null
     */
    private $name = null;

    /**
     * Parameters to pass to the route.
     *
     * @var array<string|null>
     */
    private $params = [];

    /**
     * The compiled route data.
     *
     * @var array|null
     */
    private $compiled = null;

    /**
     * @param string[]|string $methods
     * @param string $path
     * @param callable|string $callback
     * @param string[] $constraints
     * @param string|null $name
     */
    public function __construct(
        $methods,
        string $path,
        $callback,
        array $constraints = [],
        ?string $name = null
    ) {
        $this->setMethods($methods);
        $this->setPath($path);
        $this->setCallback($callback);
        $this->setConstraints($constraints);
        $this->setName($name);
    }

    /**
     * {@inheritDoc}
     */
    public function setMethods($methods): RouteInterface
    {
        $this->methods = array_map('strtoupper', (array) $methods);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * {@inheritDoc}
     */
    public function setPath(string $path): RouteInterface
    {
        $this->path = '/'.ltrim($path, '/');
        $this->compiled = null;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritDoc}
     */
    public function setCallback($callback): RouteInterface
    {
        if (is_callable($callback) || is_string($callback)) {
            $this->callback = $callback;

            return $this;
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Callback must be a callable or string; %s given.',
                gettype($callback)
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * {@inheritDoc}
     */
    public function setConstraints(array $constraints): RouteInterface
    {
        $this->constraints = $constraints;
        $this->compiled = null;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    /**
     * {@inheritDoc}
     */
    public function addConstraints(array $constraints): RouteInterface
    {
        $this->constraints = array_merge($this->constraints, $constraints);
        $this->compiled = null;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setName(?string $name): RouteInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function setParams(array $params): RouteInterface
    {
        $this->params = $params;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * {@inheritDoc}
     */
    public function addParams(array $params): RouteInterface
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function compile(): array
    {
        if ($this->compiled === null) {
            $compiled = RouteCompiler::compile($this->path, $this->constraints, $this->params);
            $this->compiled = [
                'regex' => $compiled['regex'],
                'tokens' => $compiled['tokens'],
            ];
            $this->params = $compiled['params'];
            $this->constraints = $compiled['constraints'];
        }

        return $this->compiled;
    }
}
