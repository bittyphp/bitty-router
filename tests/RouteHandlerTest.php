<?php

namespace Bitty\Tests\Router;

use Bitty\Middleware\RequestHandlerInterface;
use Bitty\Router\CallbackBuilderInterface;
use Bitty\Router\RouteHandler;
use Bitty\Router\RouteInterface;
use Bitty\Router\RouterInterface;
use Bitty\Tests\Router\TestCase;
use Bitty\Tests\Router\Stubs\InvokableStubInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class RouteHandlerTest extends TestCase
{
    /**
     * @var RouteHandler
     */
    protected $fixture = null;

    /**
     * @var RouterInterface
     */
    protected $router = null;

    /**
     * @var CallbackBuilderInterface
     */
    protected $builder = null;

    protected function setUp()
    {
        parent::setUp();

        $this->router  = $this->createMock(RouterInterface::class);
        $this->builder = $this->createMock(CallbackBuilderInterface::class);

        $this->fixture = new RouteHandler($this->router, $this->builder);
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(RequestHandlerInterface::class, $this->fixture);
    }

    public function testHandleCallsRouter()
    {
        $request  = $this->createRequest();
        $route    = $this->createRoute();
        $callback = function () {
        };

        $this->builder->method('build')->willReturn([$callback, null]);

        $this->router->expects($this->once())
            ->method('find')
            ->with($request)
            ->willReturn($route);

        $this->fixture->handle($request);
    }

    public function testHandleCallsBuilder()
    {
        $request  = $this->createRequest();
        $callback = uniqid('callback');
        $route    = $this->createRoute($callback);

        $this->router->method('find')->willReturn($route);

        $this->builder->expects($this->once())
            ->method('build')
            ->with($callback)
            ->willReturn([$this->createMock(InvokableStubInterface::class), null]);

        $this->fixture->handle($request);
    }

    /**
     * @dataProvider sampleMethods
     */
    public function testHandleTriggersCallback($method)
    {
        $request  = $this->createRequest();
        $params   = [uniqid(), uniqid()];
        $callback = uniqid('callback');
        $route    = $this->createRoute($callback, $params);
        $object   = $this->createMock(InvokableStubInterface::class);

        $this->router->method('find')->willReturn($route);
        $this->builder->method('build')->willReturn([$object, $method]);

        $object->expects($this->once())
            ->method($method ?: '__invoke')
            ->with($request, $params);

        $this->fixture->handle($request);
    }

    /**
     * @dataProvider sampleMethods
     */
    public function testHandleReturnsCallbackResponse($method)
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

        $this->assertSame($response, $actual);
    }

    public function sampleMethods()
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
     * @return ServerRequestInterface
     */
    protected function createRequest($path = '', $method = 'GET')
    {
        $uri = $this->createConfiguredMock(
            UriInterface::class,
            ['getPath' => $path]
        );

        return $this->createConfiguredMock(
            ServerRequestInterface::class,
            [
                'getUri' => $uri,
                'getMethod' => $method,
            ]
        );
    }

    /**
     * Creates a route.
     *
     * @param callback|null $callback
     * @param array $params
     *
     * @return RouteInterface
     */
    protected function createRoute($callback = null, array $params = [])
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
