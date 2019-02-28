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

    public function testAddWithName(): void
    {
        $name  = uniqid('name');
        $route = $this->createConfiguredMock(RouteInterface::class, ['getName' => $name]);

        $this->fixture->add($route);

        $actual = $this->fixture->get($name);

        self::assertSame($route, $actual);
    }

    public function testAddWithoutName(): void
    {
        $route = $this->createMock(RouteInterface::class);

        $this->fixture->add($route);

        $actual = $this->fixture->get('_route_0');

        self::assertSame($route, $actual);
    }

    public function testMultipleAddsIncrementsIdentifier(): void
    {
        $routeA = $this->createMock(RouteInterface::class);
        $routeB = $this->createMock(RouteInterface::class);

        $this->fixture->add($routeA);
        $this->fixture->add($routeB);

        $actualA = $this->fixture->get('_route_0');
        $actualB = $this->fixture->get('_route_1');

        self::assertSame($routeA, $actualA);
        self::assertSame($routeB, $actualB);
    }

    public function testAddCollection(): void
    {
        $nameA  = uniqid('name');
        $nameB  = uniqid('name');
        $routeA = $this->createConfiguredMock(RouteInterface::class, ['getName' => $nameA]);
        $routeB = $this->createConfiguredMock(RouteInterface::class, ['getName' => $nameB]);

        $collection = new RouteCollection();
        $collection->add($routeA);
        $collection->add($routeB);

        $this->fixture->addCollection($collection);

        $actual = $this->fixture->all();

        self::assertEquals([$nameA => $routeA, $nameB => $routeB], $actual);
    }

    public function testAll(): void
    {
        $nameA  = uniqid('name');
        $nameB  = uniqid('name');
        $routeA = $this->createConfiguredMock(RouteInterface::class, ['getName' => $nameA]);
        $routeB = $this->createConfiguredMock(RouteInterface::class, ['getName' => $nameB]);

        $this->fixture->add($routeA);
        $this->fixture->add($routeB);

        $actual = $this->fixture->all();

        self::assertEquals([$nameA, $nameB], array_keys($actual));
    }

    public function testHasTrue(): void
    {
        $name  = uniqid('name');
        $route = $this->createConfiguredMock(RouteInterface::class, ['getName' => $name]);

        $this->fixture->add($route);
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
        $name  = uniqid('name');
        $route = $this->createConfiguredMock(RouteInterface::class, ['getName' => $name]);

        $this->fixture->add($route);
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

    public function testSetMethods(): void
    {
        $methods = [uniqid()];
        $routeA  = $this->createMock(RouteInterface::class);
        $routeB  = $this->createMock(RouteInterface::class);

        $this->fixture->add($routeA);
        $this->fixture->add($routeB);

        $routeA->expects(self::once())
            ->method('setMethods')
            ->with($methods);

        $routeB->expects(self::once())
            ->method('setMethods')
            ->with($methods);

        $this->fixture->setMethods($methods);
    }

    public function testAddPrefix(): void
    {
        $prefix = uniqid();
        $pathA  = uniqid();
        $pathB  = uniqid();
        $routeA = $this->createConfiguredMock(RouteInterface::class, ['getPath' => $pathA]);
        $routeB = $this->createConfiguredMock(RouteInterface::class, ['getPath' => $pathB]);

        $this->fixture->add($routeA);
        $this->fixture->add($routeB);

        $routeA->expects(self::once())
            ->method('setPath')
            ->with($prefix.$pathA);

        $routeB->expects(self::once())
            ->method('setPath')
            ->with($prefix.$pathB);

        $this->fixture->addPrefix($prefix);
    }

    public function testAddNamePrefix(): void
    {
        $prefix = uniqid();
        $nameA  = uniqid();
        $nameB  = uniqid();
        $routeA = $this->createConfiguredMock(RouteInterface::class, ['getName' => $nameA]);
        $routeB = $this->createConfiguredMock(RouteInterface::class, ['getName' => null]);
        $routeC = $this->createConfiguredMock(RouteInterface::class, ['getName' => $nameB]);

        $this->fixture->add($routeA);
        $this->fixture->add($routeB);
        $this->fixture->add($routeC);

        $routeA->expects(self::once())
            ->method('setName')
            ->with($prefix.$nameA);

        $routeC->expects(self::once())
            ->method('setName')
            ->with($prefix.$nameB);

        $this->fixture->addNamePrefix($prefix);

        $actual = $this->fixture->all();

        $expected = [
            $prefix.$nameA => $routeA,
            $prefix.'_route_1' => $routeB,
            $prefix.$nameB => $routeC,
        ];
        self::assertEquals($expected, $actual);
    }

    public function testAddConstraints(): void
    {
        $constraints = [uniqid()];
        $routeA      = $this->createMock(RouteInterface::class);
        $routeB      = $this->createMock(RouteInterface::class);

        $this->fixture->add($routeA);
        $this->fixture->add($routeB);

        $routeA->expects(self::once())
            ->method('addConstraints')
            ->with($constraints);

        $routeB->expects(self::once())
            ->method('addConstraints')
            ->with($constraints);

        $this->fixture->addConstraints($constraints);
    }

    public function testAddParams(): void
    {
        $params = [uniqid()];
        $routeA = $this->createMock(RouteInterface::class);
        $routeB = $this->createMock(RouteInterface::class);

        $this->fixture->add($routeA);
        $this->fixture->add($routeB);

        $routeA->expects(self::once())
            ->method('addParams')
            ->with($params);

        $routeB->expects(self::once())
            ->method('addParams')
            ->with($params);

        $this->fixture->addParams($params);
    }
}
