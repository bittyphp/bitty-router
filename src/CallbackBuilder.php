<?php

namespace Bitty\Router;

use Bitty\Router\CallbackBuilderInterface;
use Bitty\Router\Exception\RouterException;
use Psr\Container\ContainerInterface;

class CallbackBuilder implements CallbackBuilderInterface
{
    /**
     * @var ContainerInterface
     */
    private $container = null;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function build($callback): array
    {
        if ($callback instanceof \Closure) {
            $callback = $callback->bindTo($this->container);

            return [$callback, null];
        }

        if (!is_string($callback)) {
            throw new RouterException(
                sprintf(
                    'Callback must be a string or instance of \Closure; %s given.',
                    gettype($callback)
                )
            );
        }

        [$class, $method] = $this->getClassAndMethod($callback);

        if ($this->container->has($class)) {
            $object = $this->container->get($class);
        } else {
            $object = new $class($this->container);
        }

        return [$object, $method];
    }

    /**
     * Gets the class name and optional method name from the callback string.
     *
     * @param string $callback
     *
     * @return mixed[]
     */
    private function getClassAndMethod(string $callback): array
    {
        $parts = explode(':', $callback);
        if (count($parts) === 2) {
            return $parts;
        }

        if (count($parts) !== 1) {
            throw new RouterException(
                sprintf('Callback "%s" is malformed.', $callback)
            );
        }

        return [$callback, null];
    }
}
