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

    /**
     * @param string $path
     * @param array $constraints
     * @param string $expected
     *
     * @dataProvider samplePatterns
     */
    public function testGetPattern(string $path, array $constraints, string $expected): void
    {
        $fixture = new Route([], $path, uniqid(), $constraints);

        $actual = $fixture->getPattern();

        self::assertEquals($expected, $actual);
    }

    public function samplePatterns(): array
    {
        $varA   = uniqid('a');
        $varB   = uniqid('bb');
        $varC   = uniqid('ccc');
        $valueA = '\d{'.rand(1, 9).'}';
        $valueB = '\d{'.rand(1, 9).'}';
        $valueC = '\d{'.rand(1, 9).'}';
        $pathA  = uniqid();
        $pathB  = uniqid();

        return [
            'no patterns' => [
                'path' => $pathA,
                'constraints' => [],
                'expected' => '`^'.$pathA.'$`',
            ],
            'one pattern, start' => [
                'path' => '{'.$varA.'}/'.$pathA,
                'constraints' => [$varA => $valueA],
                'expected' => '`^(?<'.$varA.'>'.$valueA.')/'.$pathA.'$`',
            ],
            'one pattern, middle' => [
                'path' => $pathA.'/{'.$varA.'}/'.$pathB,
                'constraints' => [$varA => $valueA],
                'expected' => '`^'.$pathA.'/(?<'.$varA.'>'.$valueA.')/'.$pathB.'$`',
            ],
            'one pattern, end' => [
                'path' => $pathA.'/{'.$varA.'}',
                'constraints' => [$varA => $valueA],
                'expected' => '`^'.$pathA.'/(?<'.$varA.'>'.$valueA.')$`',
            ],
            'one pattern, no constraint' => [
                'path' => $pathA.'{'.$varA.'}',
                'constraints' => [],
                'expected' => '`^'.$pathA.'(?<'.$varA.'>.+?)$`',
            ],
            'multiple patterns' => [
                'path' => $pathA.'/{'.$varA.'}{'.$varB.'}/{'.$varC.'}',
                'constraints' => [
                    $varA => $valueA,
                    $varB => $valueB,
                ],
                'expected' => '`^'.$pathA.'/(?<'.$varA.'>'.$valueA.')(?<'.$varB.'>'.$valueB.')'
                    .'/(?<'.$varC.'>.+?)$`',
            ],
            'optional pattern' => [
                'path' => $pathA.'{'.$varA.'?}'.$pathB,
                'constraints' => [$varA => $valueA],
                'expected' => '`^'.$pathA.'(?<'.$varA.'>'.$valueA.')?'.$pathB.'$`',
            ],
            'optional pattern, with dir slash' => [
                'path' => '/{'.$varA.'?}/'.$pathB,
                'constraints' => [$varA => $valueA],
                'expected' => '`^(?:/(?<'.$varA.'>'.$valueA.'))?/'.$pathB.'$`',
            ],
            'optional pattern, with dot' => [
                'path' => $pathA.'.{'.$varA.'?}',
                'constraints' => [$varA => $valueA],
                'expected' => '`^'.$pathA.'(?:\.(?<'.$varA.'>'.$valueA.'))?$`',
            ],
            'multiple optional patterns' => [
                'path' => '/{'.$varA.'?}/{'.$varB.'?}/{'.$varC.'?}/'.$pathB,
                'constraints' => [$varA => $valueA],
                'expected' => '`^(?:/(?<'.$varA.'>'.$valueA.'))?'
                    .'(?:/(?<'.$varB.'>.+?))?(?:/(?<'.$varC.'>.+?))?/'.$pathB.'$`',
            ],
            'text patterns escaped' => [
                'path' => $pathA.'`{'.$varA.'}`'.$pathB,
                'constraints' => [],
                'expected' => '`^'.$pathA.'\`(?<'.$varA.'>.+?)\`'.$pathB.'$`',
            ],
        ];
    }

    public function testGetPatternOptionalPatternSetsDefaultValue(): void
    {
        $key     = uniqid('a');
        $default = uniqid();
        $prefix  = uniqid();
        $path    = $prefix.'/{'.$key.'?'.$default.'}';
        $fixture = new Route([], $path, uniqid());

        self::assertEquals('`^'.$prefix.'(?:/(?<'.$key.'>.+?))?$`', $fixture->getPattern());
        self::assertEquals([$key => $default], $fixture->getParams());
    }
}
