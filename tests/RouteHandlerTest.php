<?php

namespace Bitty\Tests\Router;

use Bitty\Router\CallbackBuilderInterface;
use Bitty\Router\RouteHandler;
use Bitty\Router\RouteInterface;
use Bitty\Router\RouterInterface;
use Bitty\Tests\Router\Stubs\InvokableStubInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteHandlerTest extends TestCase
{
    /**
     * @var RouteHandler
     */
    protected $fixture = null;

    /**
     * @var RouterInterface|MockObject
     */
    protected $router = null;

    /**
     * @var CallbackBuilderInterface|MockObject
     */
    protected $builder = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router  = $this->createMock(RouterInterface::class);
        $this->builder = $this->createMock(CallbackBuilderInterface::class);

        $this->fixture = new RouteHandler($this->router, $this->builder);
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(RequestHandlerInterface::class, $this->fixture);
    }

    public function testHandleCallsRouter(): void
    {
        $request  = $this->createRequest();
        $route    = $this->createRoute();
        $callback = function () {
            return $this->createResponse();
        };

        $this->builder->method('build')->willReturn([$callback, null]);

        $this->router->expects(self::once())
            ->method('find')
            ->with($request)
            ->willReturn($route);

        $this->fixture->handle($request);
    }

    public function testHandleCallsBuilder(): void
    {
        $request  = $this->createRequest();
        $response = $this->createResponse();
        $callback = uniqid('callback');
        $route    = $this->createRoute($callback);

        $this->router->method('find')->willReturn($route);

        $object = $this->createMock(InvokableStubInterface::class);
        $object->method('__invoke')->willReturn($response);

        $this->builder->expects(self::once())
            ->method('build')
            ->with($callback)
            ->willReturn([$object, null]);

        $this->fixture->handle($request);
    }

    /**
     * @param string|null $method
     *
     * @dataProvider sampleMethods
     */
    public function testHandleAddsRouteParamsToRequest(?string $method): void
    {
        $request  = $this->createRequest();
        $response = $this->createResponse();
        $keyA     = uniqid();
        $keyB     = uniqid();
        $valueA   = uniqid();
        $valueB   = uniqid();
        $callback = uniqid('callback');
        $route    = $this->createRoute($callback, [$keyA => $valueA, $keyB => $valueB]);
        $object   = $this->createMock(InvokableStubInterface::class);

        $object->method('__invoke')->willReturn($response);
        $this->router->method('find')->willReturn($route);
        $this->builder->method('build')->willReturn([$object, $method]);

        $request->expects(self::exactly(2))
            ->method('withAttribute')
            ->withConsecutive([$keyA, $valueA], [$keyB, $valueB]);

        $this->fixture->handle($request);
    }

    /**
     * @param string|null $method
     *
     * @dataProvider sampleMethods
     */
    public function testHandleTriggersCallback(?string $method): void
    {
        $request  = $this->createRequest();
        $response = $this->createResponse();
        $params   = [uniqid(), uniqid()];
        $callback = uniqid('callback');
        $route    = $this->createRoute($callback, $params);
        $object   = $this->createMock(InvokableStubInterface::class);

        $this->router->method('find')->willReturn($route);
        $this->builder->method('build')->willReturn([$object, $method]);

        $object->expects(self::once())
            ->method($method ?: '__invoke')
            ->with($request)
            ->willReturn($response);

        $this->fixture->handle($request);
    }

    /**
     * @param string|null $method
     *
     * @dataProvider sampleMethods
     */
    public function testHandleReturnsCallbackResponse(?string $method): void
    {
        $request  = $this->createRequest();
        $params   = [uniqid(), uniqid()];
        $callback = uniqid('callback');
        $route    = $this->createRoute($callback, $params);
        $object   = $this->createMock(InvokableStubInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $this->router->method('find')->willReturn($route);
        $this->builder->method('build')->willReturn([$object, $method]);
        $object->method('__invoke')->willReturn($response);

        $actual = $this->fixture->handle($request);

        self::assertSame($response, $actual);
    }

    public function sampleMethods(): array
    {
        return [
            [null],
            ['__invoke'],
        ];
    }

    /**
     * Creates a request.
     *
     * @param string $path
     * @param string $method
     *
     * @return ServerRequestInterface|MockObject
     */
    protected function createRequest($path = '', $method = 'GET'): ServerRequestInterface
    {
        $uri = $this->createConfiguredMock(
            UriInterface::class,
            ['getPath' => $path]
        );

        $request = $this->createConfiguredMock(
            ServerRequestInterface::class,
            [
                'getUri' => $uri,
                'getMethod' => $method,
            ]
        );
        $request->method('withAttribute')->willReturn($request);

        return $request;
    }

    /**
     * @return ResponseInterface|MockObject
     */
    protected function createResponse(): ResponseInterface
    {
        return $this->createMock(ResponseInterface::class);
    }

    /**
     * Creates a route.
     *
     * @param callable|string|null $callback
     * @param array $params
     *
     * @return RouteInterface|MockObject
     */
    protected function createRoute($callback = null, array $params = []): RouteInterface
    {
        return $this->createConfiguredMock(
            RouteInterface::class,
            [
                'getCallback' => $callback,
                'getParams' => $params,
            ]
        );
    }
}
