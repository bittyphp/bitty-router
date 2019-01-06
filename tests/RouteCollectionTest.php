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

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new RouteCollection();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(RouteCollectionInterface::class, $this->fixture);
    }

    public function testAdd()
    {
        $methods     = ['get', 'pOsT'];
        $path        = uniqid();
        $constraints = [uniqid()];
        $name        = uniqid();
        $callback    = function () {
        };

        $this->fixture->add($methods, $path, $callback, $constraints, $name);

        $actual = $this->fixture->get($name);

        $this->assertInstanceOf(RouteInterface::class, $actual);
        $this->assertEquals(['GET', 'POST'], $actual->getMethods());
        $this->assertEquals($path, $actual->getPath());
        $this->assertEquals($callback, $actual->getCallback());
        $this->assertEquals($constraints, $actual->getConstraints());
        $this->assertEquals($name, $actual->getName());
        $this->assertEquals('route_0', $actual->getIdentifier());
    }

    public function testAddWithStringCallback()
    {
        $callback = uniqid();

        $this->fixture->add(uniqid(), uniqid(), $callback);

        $actual = $this->fixture->get('route_0');

        $this->assertEquals($callback, $actual->getCallback());
    }

    public function testAddWithoutNameUsesIdentifier()
    {
        $this->fixture->add(uniqid(), uniqid(), uniqid());

        $actual = $this->fixture->get('route_0');

        $this->assertInstanceOf(RouteInterface::class, $actual);
        $this->assertNull($actual->getName());
        $this->assertEquals('route_0', $actual->getIdentifier());
    }

    public function testMultipleAddsIncrementsIdentifier()
    {
        $nameA = uniqid();
        $nameB = uniqid();

        $this->fixture->add(uniqid(), uniqid(), uniqid(), [], $nameA);
        $this->fixture->add(uniqid(), uniqid(), uniqid(), [], $nameB);

        $actualA = $this->fixture->get($nameA);
        $actualB = $this->fixture->get($nameB);

        $this->assertEquals('route_0', $actualA->getIdentifier());
        $this->assertEquals('route_1', $actualB->getIdentifier());
    }

    public function testAddInvalidCallbackThrowsException()
    {
        $message = 'Callback must be a callable or string; NULL given.';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $this->fixture->add(uniqid(), uniqid(), null);
    }

    public function testAll()
    {
        $name = uniqid('name');

        $this->fixture->add(uniqid(), uniqid(), uniqid(), [], $name);

        $actual = $this->fixture->all();

        $this->assertEquals([$name], array_keys($actual));
    }

    public function testHasTrue()
    {
        $name = uniqid('name');

        $this->fixture->add(uniqid(), uniqid(), uniqid(), [], $name);

        $actual = $this->fixture->has($name);

        $this->assertTrue($actual);
    }

    public function testHasFalse()
    {
        $actual = $this->fixture->has(uniqid());

        $this->assertFalse($actual);
    }

    public function testGetThrowsException()
    {
        $name = uniqid();

        $message = 'No route named "'.$name.'" exists.';
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage($message);

        $this->fixture->get($name);
    }

    public function testRemoveExistingRoute()
    {
        $name = uniqid('name');

        $this->fixture->add(uniqid(), uniqid(), uniqid(), [], $name);
        $this->fixture->remove($name);

        $actual = $this->fixture->has($name);

        $this->assertFalse($actual);
    }

    public function testRemoveNonExistingRoute()
    {
        $name = uniqid('name');

        $this->fixture->remove($name);

        $actual = $this->fixture->has($name);

        $this->assertFalse($actual);
    }
}
