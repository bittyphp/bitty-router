<?php

namespace Bitty\Tests\Router;

use Bitty\Router\CallbackBuilder;
use Bitty\Router\CallbackBuilderInterface;
use Bitty\Router\Exception\RouterException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class CallbackBuilderTest extends TestCase
{
    /**
     * @var CallbackBuilder
     */
    protected $fixture = null;

    /**
     * @var ContainerInterface|MockObject
     */
    protected $container = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);

        $this->fixture = new CallbackBuilder($this->container);
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(CallbackBuilderInterface::class, $this->fixture);
    }

    public function testBuildSetsContainerOnClosure(): void
    {
        $callback = function () {
            if (!$this instanceof ContainerInterface) {
                self::fail('Container not set');
            }
        };

        $actual = $this->fixture->build($callback);

        self::assertNotSame($callback, $actual[0]);
        self::assertNull($actual[1]);
        self::assertNull($actual[0]());
    }

    /**
     * @param string $callback
     * @param string $class
     *
     * @dataProvider sampleCallbacks
     */
    public function testBuildChecksContainer(string $callback, string $class): void
    {
        $this->container->expects(self::once())
            ->method('has')
            ->with($class)
            ->willReturn(false);

        $this->fixture->build($callback);
    }

    public function testBuildGetsFromContainer(): void
    {
        $callback = \stdClass::class;
        $object   = $this->createMock($callback);

        $this->container->method('has')->willReturn(true);

        $this->container->expects(self::once())
            ->method('get')
            ->with($callback)
            ->willReturn($object);

        $this->fixture->build($callback);
    }

    /**
     * @param string $callback
     * @param string $class
     * @param string|null $method
     *
     * @dataProvider sampleCallbacks
     */
    public function testBuildInvokable(
        string $callback,
        string $class,
        ?string $method
    ): void {
        $actual = $this->fixture->build($callback);

        self::assertInstanceOf($class, $actual[0]);
        self::assertEquals($method, $actual[1]);
    }

    public function sampleCallbacks(): array
    {
        $method = uniqid('method');

        return [
            [\stdClass::class, \stdClass::class, null],
            [\stdClass::class.':'.$method, \stdClass::class, $method],
        ];
    }

    /**
     * @param mixed $invalid
     * @param string $expected
     *
     * @dataProvider sampleInvalidCallbacks
     */
    public function testInvalidCallbackThrowsException($invalid, string $expected): void
    {
        $message = 'Callback must be a string or instance of \Closure; '.$expected.' given.';
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage($message);

        $this->fixture->build($invalid);
    }

    public function sampleInvalidCallbacks(): array
    {
        return [
            [null, 'NULL'],
            [[], 'array'],
            [(object) [], 'object'],
        ];
    }

    public function testMalformedCallbackThrowsException(): void
    {
        $callback = uniqid().':'.uniqid().':'.uniqid();

        $message = 'Callback "'.$callback.'" is malformed.';
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage($message);

        $this->fixture->build($callback);
    }
}
