<?php

namespace Bitty\Tests\Router;

use Bitty\Router\Exception\NotFoundException;
use Bitty\Router\RoutingMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoutingMiddlewareTest extends TestCase
{
    /**
     * @var RoutingMiddleware
     */
    protected $fixture = null;

    /**
     * @var RequestHandlerInterface|MockObject
     */
    protected $routeHandler = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->routeHandler = $this->createMock(RequestHandlerInterface::class);

        $this->fixture = new RoutingMiddleware($this->routeHandler);
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(MiddlewareInterface::class, $this->fixture);
    }

    public function testProcessCallsRouteHandler(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $this->routeHandler->expects(self::once())
            ->method('handle')
            ->with($request);

        $this->fixture->process($request, $handler);
    }

    public function testProcessReturnsResponse(): void
    {
        $request  = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler  = $this->createMock(RequestHandlerInterface::class);

        $this->routeHandler->method('handle')->willReturn($response);

        $actual = $this->fixture->process($request, $handler);

        self::assertSame($response, $actual);
    }

    public function testProcessDoesNotCallNextHandler(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $handler->expects(self::never())->method('handle');

        $this->fixture->process($request, $handler);
    }

    public function testProcessCallsNextHandlerOnException(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $exception = new NotFoundException();
        $this->routeHandler->method('handle')->willThrowException($exception);

        $handler->expects(self::once())
            ->method('handle')
            ->with($request);

        $this->fixture->process($request, $handler);
    }

    public function testProcessReturnsResponseOnException(): void
    {
        $request  = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler  = $this->createMock(RequestHandlerInterface::class);

        $exception = new NotFoundException();
        $this->routeHandler->method('handle')->willThrowException($exception);
        $handler->method('handle')->willReturn($response);

        $actual = $this->fixture->process($request, $handler);

        self::assertSame($response, $actual);
    }
}
