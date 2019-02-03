<?php

namespace Bitty\Tests\Router;

use Bitty\Router\Route;
use Bitty\Router\RouteInterface;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $fixture = new Route([], uniqid(), uniqid());

        self::assertInstanceOf(RouteInterface::class, $fixture);
    }

    public function testGetIdentifier(): void
    {
        $fixture = new Route([], uniqid(), uniqid());

        $actual = $fixture->getIdentifier();

        self::assertEquals('route_0', $actual);
    }

    public function testGetMethods(): void
    {
        $methodA = uniqid('a');
        $methodB = uniqid('b');
        $fixture = new Route([$methodA, $methodB], uniqid(), uniqid());

        $actual = $fixture->getMethods();

        self::assertEquals([strtoupper($methodA), strtoupper($methodB)], $actual);
    }

    public function testSetMethods(): void
    {
        $methodA = uniqid('a');
        $methodB = uniqid('b');
        $fixture = new Route([], uniqid(), uniqid());
        $fixture->setMethods([$methodA, $methodB]);

        $actual = $fixture->getMethods();

        self::assertEquals([strtoupper($methodA), strtoupper($methodB)], $actual);
    }

    public function testGetPath(): void
    {
        $path    = uniqid();
        $fixture = new Route([], $path, uniqid());

        $actual = $fixture->getPath();

        self::assertEquals($path, $actual);
    }

    public function testSetPath(): void
    {
        $path    = uniqid();
        $fixture = new Route([], uniqid(), uniqid());
        $fixture->setPath($path);

        $actual = $fixture->getPath();

        self::assertEquals($path, $actual);
    }

    public function testGetCallbackWithCallable(): void
    {
        $callable = function () {
        };

        $fixture = new Route([], uniqid(), $callable);

        $actual = $fixture->getCallback();

        self::assertSame($callable, $actual);
    }

    public function testSetCallbackWithCallable(): void
    {
        $callable = function () {
        };

        $fixture = new Route([], uniqid(), uniqid());
        $fixture->setCallback($callable);

        $actual = $fixture->getCallback();

        self::assertSame($callable, $actual);
    }

    public function testGetCallbackWithString(): void
    {
        $callable = uniqid();

        $fixture = new Route([], uniqid(), $callable);

        $actual = $fixture->getCallback();

        self::assertSame($callable, $actual);
    }

    public function testSetCallbackWithString(): void
    {
        $callable = uniqid();

        $fixture = new Route([], uniqid(), uniqid());
        $fixture->setCallback($callable);

        $actual = $fixture->getCallback();

        self::assertSame($callable, $actual);
    }

    /**
     * @param mixed $callback
     * @param string $expected
     *
     * @dataProvider sampleInvalidCallbacks
     */
    public function testInvalidCallbackThrowsException($callback, string $expected): void
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Callback must be a callable or string; '.$expected.' given.');

        new Route([], uniqid(), $callback);
    }

    /**
     * @param mixed $callback
     * @param string $expected
     *
     * @dataProvider sampleInvalidCallbacks
     */
    public function testSetCallbackThrowsException($callback, string $expected): void
    {
        $fixture = new Route([], uniqid(), uniqid());

        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Callback must be a callable or string; '.$expected.' given.');

        $fixture->setCallback($callback);
    }

    public function sampleInvalidCallbacks(): array
    {
        return [
            'null' => [null, 'NULL'],
            'object' => [(object) [], 'object'],
            'false' => [false, 'boolean'],
            'true' => [true, 'boolean'],
            'int' => [rand(), 'integer'],
        ];
    }

    public function testGetConstraints(): void
    {
        $constraints = [uniqid()];

        $fixture = new Route([], uniqid(), uniqid(), $constraints);

        $actual = $fixture->getConstraints();

        self::assertEquals($constraints, $actual);
    }

    public function testSetConstraints(): void
    {
        $constraints = [uniqid()];

        $fixture = new Route([], uniqid(), uniqid());
        $fixture->setConstraints($constraints);

        $actual = $fixture->getConstraints();

        self::assertEquals($constraints, $actual);
    }

    public function testGetName(): void
    {
        $name = uniqid();

        $fixture = new Route([], uniqid(), uniqid(), [], $name);

        $actual = $fixture->getName();

        self::assertEquals($name, $actual);
    }

    public function testSetName(): void
    {
        $name = uniqid();

        $fixture = new Route([], uniqid(), uniqid());
        $fixture->setName($name);

        $actual = $fixture->getName();

        self::assertEquals($name, $actual);
    }

    public function testSetParams(): void
    {
        $params = [uniqid()];

        $fixture = new Route([], uniqid(), uniqid());
        $fixture->setParams($params);

        $actual = $fixture->getParams();

        self::assertEquals($params, $actual);
    }

    public function testGetPattern(): void
    {
        $key    = uniqid();
        $value  = '\d{'.rand(1, 9).'}';
        $prefix = uniqid().'/';
        $suffix = '/'.uniqid();
        $path   = $prefix.'{'.$key.'}'.$suffix;

        $fixture = new Route([], $path, uniqid(), [$key => $value]);

        $actual = $fixture->getPattern();

        self::assertEquals($prefix.'(?<'.$key.'>'.$value.')'.$suffix, $actual);
    }
}
