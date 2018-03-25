<?php

namespace Bitty\Router;

use Bitty\Middleware\RequestHandlerInterface;
use Bitty\Router\CallbackBuilderInterface;
use Bitty\Router\RouterInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouteHandler implements RequestHandlerInterface
{
    /**
     * @var RouterInterface
     */
    protected $router = null;

    /**
     * @var CallbackBuilderInterface
     */
    protected $builder = null;

    /**
     * @param RouterInterface $router
     * @param CallbackBuilderInterface $builder
     */
    public function __construct(RouterInterface $router, CallbackBuilderInterface $builder)
    {
        $this->router  = $router;
        $this->builder = $builder;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request)
    {
        $route    = $this->router->find($request);
        $callback = $route->getCallback();
        $params   = $route->getParams();

        list($controller, $action) = $this->builder->build($callback);

        foreach ($params as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        if (null !== $action) {
            return call_user_func_array(
                [$controller, $action],
                [$request]
            );
        }

        return $controller($request);
    }
}
