<?php

namespace Bitty\Tests\Router;

use Bitty\Router\Exception\NotFoundException;
use Bitty\Router\Route;
use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteMatcher;
use Bitty\Router\RouteMatcherInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class RouteMatcherTest extends TestCase
{
    /**
     * @var RouteMatcher
     */
    protected $fixture = null;

    /**
     * @var RouteCollectionInterface|MockObject
     */
    protected $routes = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->routes = $this->createMock(RouteCollectionInterface::class);

        $this->fixture = new RouteMatcher($this->routes);
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(RouteMatcherInterface::class, $this->fixture);
    }

    /**
     * @param array $routeData
     * @param string $path
     * @param string $method
     * @param string $expectedName
     * @param array $expecedParams
     *
     * @dataProvider sampleMatch
     */
    public function testMatch(
        array $routeData,
        string $path,
        string $method,
        string $expectedName,
        array $expecedParams
    ): void {
        $request = $this->createRequest($method, $path);
        $routes  = [];
        foreach ($routeData as $data) {
            $routes[] = $this->createRoute($data[0], $data[1], $data[2], $data[3], $data[4]);
        }

        $this->routes->method('all')->willReturn($routes);

        $actual = $this->fixture->match($request);

        self::assertEquals($expectedName, $actual->getName());
        self::assertEquals($expecedParams, $actual->getParams());
    }

    public function sampleMatch(): array
    {
        $nameA    = uniqid('name');
        $nameB    = uniqid('name');
        $pathA    = '/'.uniqid('path');
        $pathB    = '/'.uniqid('path');
        $paramA   = uniqid('param');
        $paramB   = uniqid('param');
        $callback = function () {
        };

        return [
            'open route' => [
                'routes' => [
                    [[], $pathA, $callback, [], $nameA],
                ],
                'path' => $pathA,
                'method' => 'GET',
                'expectedName' => $nameA,
                'expectedParams' => [],
            ],
            'simple route' => [
                'routes' => [
                    ['GET', $pathA, $callback, [], $nameA],
                ],
                'path' => $pathA,
                'method' => 'GET',
                'expectedName' => $nameA,
                'expectedParams' => [],
            ],
            'simple route, multiple methods' => [
                'routes' => [
                    [['GET', 'POST'], $pathA, $callback, [], $nameA],
                ],
                'path' => $pathA,
                'method' => 'POST',
                'expectedName' => $nameA,
                'expectedParams' => [],
            ],
            'multiple simple routes, same path' => [
                'routes' => [
                    ['GET', $pathA, $callback, [], $nameA],
                    ['POST', $pathA, $callback, [], $nameB],
                ],
                'path' => $pathA,
                'method' => 'POST',
                'expectedName' => $nameB,
                'expectedParams' => [],
            ],
            'multiple simple routes, unique paths' => [
                'routes' => [
                    ['GET', $pathA, $callback, [], $nameA],
                    ['POST', $pathB, $callback, [], $nameB],
                ],
                'path' => $pathB,
                'method' => 'POST',
                'expectedName' => $nameB,
                'expectedParams' => [],
            ],
            'constraint route' => [
                'routes' => [
                    ['GET', $pathA.'/{paramA}', $callback, ['paramA' => '.+'], $nameA],
                ],
                'path' => $pathA.'/'.$paramA,
                'method' => 'GET',
                'expectedName' => $nameA,
                'expectedParams' => ['paramA' => $paramA],
            ],
            'constraint route, multiple params' => [
                'routes' => [
                    ['GET', $pathA.'/{paramA}/{paramB}', $callback, ['paramA' => '\w+', 'paramB' => '.+'], $nameA],
                ],
                'path' => $pathA.'/'.$paramA.'/'.$paramB,
                'method' => 'GET',
                'expectedName' => $nameA,
                'expectedParams' => ['paramA' => $paramA, 'paramB' => $paramB],
            ],
            'multiple constraint routes, same path' => [
                'routes' => [
                    ['GET', $pathA.'/{paramA}', $callback, ['paramA' => '\d+'], $nameA],
                    ['GET', $pathA.'/{paramA}', $callback, ['paramA' => '\w+'], $nameB],
                ],
                'path' => $pathA.'/'.$paramA,
                'method' => 'GET',
                'expectedName' => $nameB,
                'expectedParams' => ['paramA' => $paramA],
            ],
        ];
    }

    public function testMatchThrowsException(): void
    {
        $request = $this->createRequest();
        $this->routes->method('all')->willReturn([]);

        $message = 'Route not found';
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage($message);

        $this->fixture->match($request);
    }

    /**
     * Creates a route.
     *
     * @param string[]|string $methods
     * @param string $path
     * @param callable $callback
     * @param array $constraints
     * @param string $name
     *
     * @return Route
     */
    protected function createRoute(
        $methods,
        $path,
        $callback,
        array $constraints,
        $name
    ) {
        return new Route($methods, $path, $callback, $constraints, $name);
    }

    /**
     * Creates a request.
     *
     * @param string $method
     * @param string $path
     *
     * @return ServerRequestInterface
     */
    protected function createRequest($method = 'GET', $path = '/')
    {
        $uri = $this->createConfiguredMock(UriInterface::class, ['getPath' => $path]);

        return $this->createConfiguredMock(
            ServerRequestInterface::class,
            [
                'getMethod' => $method,
                'getUri' => $uri,
            ]
        );
    }
}
