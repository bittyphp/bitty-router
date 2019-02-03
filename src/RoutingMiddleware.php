<?php

namespace Bitty\Router;

use Bitty\Router\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoutingMiddleware implements MiddlewareInterface
{
    /**
     * @var RequestHandlerInterface
     */
    private $routeHandler = null;

    /**
     * @param RequestHandlerInterface $routeHandler
     */
    public function __construct(RequestHandlerInterface $routeHandler)
    {
        $this->routeHandler = $routeHandler;
    }

    /**
     * {@inheritDoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {
            $response = $this->routeHandler->handle($request);
        } catch (NotFoundException $e) {
            return $handler->handle($request);
        }

        return $response;
    }
}
