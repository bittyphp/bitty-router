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
        $this->pattern = null;

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
        $this->pattern = null;

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
        $this->pattern = null;

        return $this;
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
            $deliminator.'\{(\w+)(<.*?>)?(\?[^\}]*?)?\}'.$deliminator,
            $this->path,
            $matches,
            PREG_SET_ORDER|PREG_OFFSET_CAPTURE
        );

        foreach ($matches as $match) {
            $string = $match[0][0];
            /** @var int $offset */
            $offset = $match[0][1];
            $name = $match[1][0];

            if (isset($match[2]) && !empty($match[2][0])) {
                $this->constraints[$name] = substr($match[2][0], 1, -1);
            }

            $regex = '(?<'.$name.'>'.($this->constraints[$name] ?? '.+?').')';
            $previousText = substr($this->path, $pos, $offset - $pos);
            $previousChar = substr($previousText, -1);
            $pos = $offset + strlen($string);

            if (isset($match[3])) {
                $default = substr($match[3][0], 1);
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
}
