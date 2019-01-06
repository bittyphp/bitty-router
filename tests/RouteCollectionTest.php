<?php

namespace Bitty\Tests\Router;

use Bitty\Router\Exception\NotFoundException;
use Bitty\Router\RouteCollection;
use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteInterface;
use PHPUnit\Framework\TestCase;

class RouteCollectionTest extends TestCase
{
    /**
     * @var RouteCollection
     */
    protected $fixture = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new RouteCollection();
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(RouteCollectionInterface::class, $this->fixture);
    }

    public function testAdd(): void
    {
        $methods     = ['get', 'pOsT'];
        $path        = uniqid();
        $constraints = [uniqid()];
        $name        = uniqid();
        $callback    = function () {
        };

        $this->fixture->add($methods, $path, $callback, $constraints, $name);

        $actual = $this->fixture->get($name);

        self::assertInstanceOf(RouteInterface::class, $actual);
        self::assertEquals(['GET', 'POST'], $actual->getMethods());
        self::assertEquals($path, $actual->getPath());
        self::assertEquals($callback, $actual->getCallback());
        self::assertEquals($constraints, $actual->getConstraints());
        self::assertEquals($name, $actual->getName());
        self::assertEquals('route_0', $actual->getIdentifier());
    }

    public function testAddWithStringCallback(): void
    {
        $callback = uniqid();

        $this->fixture->add(uniqid(), uniqid(), $callback);

        $actual = $this->fixture->get('route_0');

        self::assertEquals($callback, $actual->getCallback());
    }

    public function testAddWithoutNameUsesIdentifier(): void
    {
        $this->fixture->add(uniqid(), uniqid(), uniqid());

        $actual = $this->fixture->get('route_0');

        self::assertInstanceOf(RouteInterface::class, $actual);
        self::assertNull($actual->getName());
        self::assertEquals('route_0', $actual->getIdentifier());
    }

    public function testMultipleAddsIncrementsIdentifier(): void
    {
        $nameA = uniqid();
        $nameB = uniqid();

        $this->fixture->add(uniqid(), uniqid(), uniqid(), [], $nameA);
        $this->fixture->add(uniqid(), uniqid(), uniqid(), [], $nameB);

        $actualA = $this->fixture->get($nameA);
        $actualB = $this->fixture->get($nameB);

        self::assertEquals('route_0', $actualA->getIdentifier());
        self::assertEquals('route_1', $actualB->getIdentifier());
    }

    public function testAddInvalidCallbackThrowsException(): void
    {
        $message = 'Callback must be a callable or string; NULL given.';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $this->fixture->add(uniqid(), uniqid(), null);
    }

    public function testAll(): void
    {
        $name = uniqid('name');

        $this->fixture->add(uniqid(), uniqid(), uniqid(), [], $name);

        $actual = $this->fixture->all();

        self::assertEquals([$name], array_keys($actual));
    }

    public function testHasTrue(): void
    {
        $name = uniqid('name');

        $this->fixture->add(uniqid(), uniqid(), uniqid(), [], $name);

        $actual = $this->fixture->has($name);

        self::assertTrue($actual);
    }

    public function testHasFalse(): void
    {
        $actual = $this->fixture->has(uniqid());

        self::assertFalse($actual);
    }

    public function testGetThrowsException(): void
    {
        $name = uniqid();

        $message = 'No route named "'.$name.'" exists.';
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage($message);

        $this->fixture->get($name);
    }

    public function testRemoveExistingRoute(): void
    {
        $name = uniqid('name');

        $this->fixture->add(uniqid(), uniqid(), uniqid(), [], $name);
        $this->fixture->remove($name);

        $actual = $this->fixture->has($name);

        self::assertFalse($actual);
    }

    public function testRemoveNonExistingRoute(): void
    {
        $name = uniqid('name');

        $this->fixture->remove($name);

        $actual = $this->fixture->has($name);

        self::assertFalse($actual);
    }
}
