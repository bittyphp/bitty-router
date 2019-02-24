<?php

namespace Bitty\Tests\Router;

use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteInterface;
use Bitty\Router\RouteMatcherInterface;
use Bitty\Router\Router;
use Bitty\Router\RouterInterface;
use Bitty\Router\UriGeneratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class RouterTest extends TestCase
{
    /**
     * @var Router
     */
    protected $fixture = null;

    /**
     * @var RouteCollectionInterface|MockObject
     */
    protected $routes = null;

    /**
     * @var RouteMatcherInterface|MockObject
     */
    protected $matcher = null;

    /**
     * @var UriGeneratorInterface|MockObject
     */
    protected $uriGenerator = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->routes       = $this->createMock(RouteCollectionInterface::class);
        $this->matcher      = $this->createMock(RouteMatcherInterface::class);
        $this->uriGenerator = $this->createMock(UriGeneratorInterface::class);

        $this->fixture = new Router($this->routes, $this->matcher, $this->uriGenerator);
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(RouterInterface::class, $this->fixture);
    }

    public function testAdd(): void
    {
        $methods     = [uniqid('method'), uniqid('method')];
        $path        = uniqid('path');
        $callable    = uniqid();
        $constraints = [uniqid('key') => uniqid('value')];
        $name        = uniqid('name');

        $spy = self::once();
        $this->routes->expects($spy)
            ->method('add')
            ->with(self::isInstanceOf(RouteInterface::class));

        $actual = $this->fixture->add($methods, $path, $callable, $constraints, $name);

        $route = $spy->getInvocations()[0]->getParameters()[0];
        self::assertSame($route, $actual);
        self::assertEquals(array_map('strtoupper', $methods), $actual->getMethods());
        self::assertEquals($path, $actual->getPath());
        self::assertEquals($callable, $actual->getCallback());
        self::assertEquals($constraints, $actual->getConstraints());
        self::assertEquals($name, $actual->getName());
    }

    public function testHas(): void
    {
        $name = uniqid();
        $has  = (bool) rand(0, 1);

        $this->routes->expects(self::once())
            ->method('has')
            ->with($name)
            ->willReturn($has);

        $actual = $this->fixture->has($name);

        self::assertEquals($has, $actual);
    }

    public function testGet(): void
    {
        $name  = uniqid();
        $route = $this->createMock(RouteInterface::class);

        $this->routes->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($route);

        $actual = $this->fixture->get($name);

        self::assertSame($route, $actual);
    }

    public function testFind(): void
    {
        $route   = $this->createMock(RouteInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $this->matcher->expects(self::once())
            ->method('match')
            ->with($request)
            ->willReturn($route);

        $actual = $this->fixture->find($request);

        self::assertSame($route, $actual);
    }

    public function testGenerateUri(): void
    {
        $name   = uniqid('name');
        $params = [uniqid('param'), uniqid('param')];
        $type   = uniqid('type');
        $uri    = uniqid('uri');

        $this->uriGenerator->expects(self::once())
            ->method('generate')
            ->with($name, $params, $type)
            ->willReturn($uri);

        $actual = $this->fixture->generateUri($name, $params, $type);

        self::assertEquals($uri, $actual);
    }
}
