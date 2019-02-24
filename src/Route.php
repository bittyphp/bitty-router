<?php

namespace Bitty\Router;

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
     * The URI pattern to match.
     *
     * @var string|null
     */
    private $pattern = null;

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
     * Sets the route methods.
     *
     * @param string[]|string $methods List of request methods to allow.
     */
    public function setMethods($methods): void
    {
        $this->methods = array_map('strtoupper', (array) $methods);
    }

    /**
     * {@inheritDoc}
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Sets the route path.
     *
     * @param string $path Route path.
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
        $this->pattern = null;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Sets the route callback.
     *
     * @param callable|string $callback Callback to call.
     *
     * @throws \InvalidArgumentException
     */
    public function setCallback($callback): void
    {
        if (is_callable($callback) || is_string($callback)) {
            $this->callback = $callback;

            return;
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
     * Sets the route constraints.
     *
     * @param string[] $constraints List of constraints for route variables.
     */
    public function setConstraints(array $constraints): void
    {
        $this->constraints = $constraints;
        $this->pattern = null;
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
    public function getPattern(): string
    {
        if ($this->pattern !== null) {
            return $this->pattern;
        }

        $deliminator = '`';
        $separators = '/.';
        $this->pattern = $deliminator.'^';

        $pos = 0;
        $matches = [];
        preg_match_all(
            $deliminator.'\{(\w+)(\?[^\}]*?)?\}'.$deliminator,
            $this->path,
            $matches,
            PREG_SET_ORDER|PREG_OFFSET_CAPTURE
        );

        foreach ($matches as $match) {
            $string = $match[0][0];
            /** @var int $offset */
            $offset = $match[0][1];
            $name = $match[1][0];
            $regex = '(?<'.$name.'>'.($this->constraints[$name] ?? '.+?').')';
            $previousText = substr($this->path, $pos, $offset - $pos);
            $previousChar = substr($previousText, -1);
            $pos = $offset + strlen($string);

            if (isset($match[2])) {
                $default = substr($match[2][0], 1);
                $this->params[$name] = $default ?: null;
                if (preg_match($deliminator.'['.$separators.']'.$deliminator, $previousChar)) {
                    $previousText = substr($previousText, 0, -1);
                    $regex = '(?:'.preg_quote($previousChar, $deliminator).$regex.')?';
                } else {
                    $regex .= '?';
                }
            }

            $this->pattern .= preg_quote($previousText, $deliminator).$regex;
        }

        $remainingText = substr($this->path, $pos);
        $this->pattern .= preg_quote($remainingText, $deliminator);
        $this->pattern .= '$'.$deliminator;

        return $this->pattern;
    }

    /**
     * Sets the route name.
     *
     * @param string|null $name Route name.
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets the route parameters.
     *
     * @param array<string|null> $params Parameters to pass to the route.
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * {@inheritDoc}
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
