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

        $self   = $fixture->setMethods([$methodA, $methodB]);
        $actual = $fixture->getMethods();

        self::assertSame($fixture, $self);
        self::assertEquals([strtoupper($methodA), strtoupper($methodB)], $actual);
    }

    public function testGetPath(): void
    {
        $path    = uniqid('/');
        $fixture = new Route([], $path, uniqid());

        $actual = $fixture->getPath();

        self::assertEquals($path, $actual);
    }

    /**
     * @param string $path
     * @param string $expected
     *
     * @dataProvider samplePaths
     */
    public function testSetPath(string $path, string $expected): void
    {
        $fixture = new Route([], uniqid(), uniqid());

        $self   = $fixture->setPath($path);
        $actual = $fixture->getPath();

        self::assertSame($fixture, $self);
        self::assertEquals($expected, $actual);
    }

    public function samplePaths(): array
    {
        $path = uniqid();

        return [
            'adds slash' => [
                'path' => $path,
                'expected' => '/'.$path,
            ],
            'removes extra slashs' => [
                'path' => '/////'.$path,
                'expected' => '/'.$path,
            ],
        ];
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

        $self   = $fixture->setCallback($callable);
        $actual = $fixture->getCallback();

        self::assertSame($fixture, $self);
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

        $self   = $fixture->setCallback($callable);
        $actual = $fixture->getCallback();

        self::assertSame($fixture, $self);
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

        $self   = $fixture->setConstraints($constraints);
        $actual = $fixture->getConstraints();

        self::assertSame($fixture, $self);
        self::assertEquals($constraints, $actual);
    }

    /**
     * @param mixed[] $existing
     * @param mixed[] $add
     * @param mixed[] $expected
     *
     * @dataProvider sampleAddData
     */
    public function testAddConstraints(array $existing, array $add, array $expected): void
    {
        $fixture = new Route([], uniqid(), uniqid(), $existing);
        $fixture->addConstraints($add);

        $actual = $fixture->getConstraints();

        self::assertEquals($expected, $actual);
    }

    public function sampleAddData(): array
    {
        $keyA   = uniqid('key');
        $keyB   = uniqid('key');
        $valueA = uniqid('value');
        $valueB = uniqid('value');

        return [
            'no existing, no add' => [
                'existing' => [],
                'add' => [],
                'expected' => [],
            ],
            'no existing, one add' => [
                'existing' => [],
                'add' => [$keyA => $valueA],
                'expected' => [$keyA => $valueA],
            ],
            'no existing, multiple adds' => [
                'existing' => [],
                'add' => [$keyA => $valueA, $keyB => $valueB],
                'expected' => [$keyA => $valueA, $keyB => $valueB],
            ],
            'one existing, one add' => [
                'existing' => [$keyA => $valueA],
                'add' => [$keyB => $valueB],
                'expected' => [$keyA => $valueA, $keyB => $valueB],
            ],
            'one existing, one overwrite, one add' => [
                'existing' => [$keyA => $valueA],
                'add' => [$keyA => $valueB, $keyB => $valueA],
                'expected' => [$keyA => $valueB, $keyB => $valueA],
            ],
            'multiple existing, no add' => [
                'existing' => [$keyA => $valueA, $keyB => $valueB],
                'add' => [],
                'expected' => [$keyA => $valueA, $keyB => $valueB],
            ],
        ];
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

        $self   = $fixture->setName($name);
        $actual = $fixture->getName();

        self::assertSame($fixture, $self);
        self::assertEquals($name, $actual);
    }

    public function testSetParams(): void
    {
        $params = [uniqid()];

        $fixture = new Route([], uniqid(), uniqid());

        $self   = $fixture->setParams($params);
        $actual = $fixture->getParams();

        self::assertSame($fixture, $self);
        self::assertEquals($params, $actual);
    }

    /**
     * @param mixed[] $existing
     * @param mixed[] $add
     * @param mixed[] $expected
     *
     * @dataProvider sampleAddData
     */
    public function testAddParams(array $existing, array $add, array $expected): void
    {
        $fixture = new Route([], uniqid(), uniqid());
        $fixture->setParams($existing);

        $self   = $fixture->addParams($add);
        $actual = $fixture->getParams();

        self::assertSame($fixture, $self);
        self::assertEquals($expected, $actual);
    }

    /**
     * @param string $path
     * @param array $constraints
     * @param array $expected
     *
     * @dataProvider sampleCompiled
     */
    public function testCompile(string $path, array $constraints, array $expected): void
    {
        $fixture = new Route([], $path, uniqid(), $constraints);

        $actualA = $fixture->compile();
        $actualB = $fixture->compile(); // this should be a cached copy

        self::assertEquals($expected, $actualA);
        self::assertEquals($expected, $actualB);
    }

    public function sampleCompiled(): array
    {
        $varA   = uniqid('a');
        $varB   = uniqid('bb');
        $varC   = uniqid('ccc');
        $valueA = '\d{'.rand(1, 9).'}';
        $valueB = '\d{'.rand(1, 9).'}';
        $valueC = '\d{'.rand(1, 9).'}';
        $pathA  = uniqid('/');
        $pathB  = uniqid('/');

        return [
            'no patterns' => [
                'path' => $pathA,
                'constraints' => [],
                'expected' => [
                    'regex' => '`^'.$pathA.'$`',
                    'tokens' => [],
                ],
            ],
            'one pattern, start' => [
                'path' => '/{'.$varA.'}/'.$pathA,
                'constraints' => [$varA => $valueA],
                'expected' => [
                    'regex' => '`^/(?<'.$varA.'>'.$valueA.')/'.$pathA.'$`',
                    'tokens' => [
                        [
                            'name' => $varA,
                            'optional' => false,
                            'regex' => '(?<'.$varA.'>'.$valueA.')',
                            'prefix' => '/',
                        ],
                    ],
                ],
            ],
            'one pattern, middle' => [
                'path' => $pathA.'/{'.$varA.'}/'.$pathB,
                'constraints' => [$varA => $valueA],
                'expected' => [
                    'regex' => '`^'.$pathA.'/(?<'.$varA.'>'.$valueA.')/'.$pathB.'$`',
                    'tokens' => [
                        [
                            'name' => $varA,
                            'optional' => false,
                            'regex' => '(?<'.$varA.'>'.$valueA.')',
                            'prefix' => $pathA.'/',
                        ],
                    ],
                ],
            ],
            'one pattern, end' => [
                'path' => $pathA.'/{'.$varA.'}',
                'constraints' => [$varA => $valueA],
                'expected' => [
                    'regex' => '`^'.$pathA.'/(?<'.$varA.'>'.$valueA.')$`',
                    'tokens' => [
                        [
                            'name' => $varA,
                            'optional' => false,
                            'regex' => '(?<'.$varA.'>'.$valueA.')',
                            'prefix' => $pathA.'/',
                        ],
                    ],
                ],
            ],
            'one pattern, no constraint' => [
                'path' => $pathA.'{'.$varA.'}',
                'constraints' => [],
                'expected' => [
                    'regex' => '`^'.$pathA.'(?<'.$varA.'>.+?)$`',
                    'tokens' => [
                        [
                            'name' => $varA,
                            'optional' => false,
                            'regex' => '(?<'.$varA.'>.+?)',
                            'prefix' => $pathA,
                        ],
                    ],
                ],
            ],
            'multiple patterns' => [
                'path' => $pathA.'/{'.$varA.'}{'.$varB.'}/{'.$varC.'}',
                'constraints' => [
                    $varA => $valueA,
                    $varB => $valueB,
                ],
                'expected' => [
                    'regex' => '`^'.$pathA.'/(?<'.$varA.'>'.$valueA.')(?<'.$varB.'>'.$valueB.')'
                        .'/(?<'.$varC.'>.+?)$`',
                    'tokens' => [
                        [
                            'name' => $varA,
                            'optional' => false,
                            'regex' => '(?<'.$varA.'>'.$valueA.')',
                            'prefix' => $pathA.'/',
                        ],
                        [
                            'name' => $varB,
                            'optional' => false,
                            'regex' => '(?<'.$varB.'>'.$valueB.')',
                            'prefix' => '',
                        ],
                        [
                            'name' => $varC,
                            'optional' => false,
                            'regex' => '(?<'.$varC.'>.+?)',
                            'prefix' => '/',
                        ],
                    ],
                ],
            ],
            'optional pattern' => [
                'path' => $pathA.'{'.$varA.'?}'.$pathB,
                'constraints' => [$varA => $valueA],
                'expected' => [
                    'regex' => '`^'.$pathA.'(?<'.$varA.'>'.$valueA.')?'.$pathB.'$`',
                    'tokens' => [
                        [
                            'name' => $varA,
                            'optional' => true,
                            'regex' => '(?<'.$varA.'>'.$valueA.')',
                            'prefix' => $pathA,
                        ],
                    ],
                ],
            ],
            'optional pattern, with dir slash' => [
                'path' => '/{'.$varA.'?}/'.$pathB,
                'constraints' => [$varA => $valueA],
                'expected' => [
                    'regex' => '`^(?:/(?<'.$varA.'>'.$valueA.'))?/'.$pathB.'$`',
                    'tokens' => [
                        [
                            'name' => $varA,
                            'optional' => true,
                            'regex' => '(?:/(?<'.$varA.'>'.$valueA.'))',
                            'prefix' => '',
                        ],
                    ],
                ],
            ],
            'optional pattern, with dot' => [
                'path' => $pathA.'.{'.$varA.'?}',
                'constraints' => [$varA => $valueA],
                'expected' => [
                    'regex' => '`^'.$pathA.'(?:\.(?<'.$varA.'>'.$valueA.'))?$`',
                    'tokens' => [
                        [
                            'name' => $varA,
                            'optional' => true,
                            'regex' => '(?:\.(?<'.$varA.'>'.$valueA.'))',
                            'prefix' => $pathA,
                        ],
                    ],
                ],
            ],
            'multiple optional patterns' => [
                'path' => '/{'.$varA.'?}/{'.$varB.'?}/{'.$varC.'?}/'.$pathB,
                'constraints' => [$varA => $valueA],
                'expected' => [
                    'regex' => '`^(?:/(?<'.$varA.'>'.$valueA.'))?'
                        .'(?:/(?<'.$varB.'>.+?))?(?:/(?<'.$varC.'>.+?))?/'.$pathB.'$`',
                    'tokens' => [
                        [
                            'name' => $varA,
                            'optional' => true,
                            'regex' => '(?:/(?<'.$varA.'>'.$valueA.'))',
                            'prefix' => '',
                        ],
                        [
                            'name' => $varB,
                            'optional' => true,
                            'regex' => '(?:/(?<'.$varB.'>.+?))',
                            'prefix' => '',
                        ],
                        [
                            'name' => $varC,
                            'optional' => true,
                            'regex' => '(?:/(?<'.$varC.'>.+?))',
                            'prefix' => '',
                        ],
                    ],
                ],
            ],
            'text patterns escaped' => [
                'path' => $pathA.'`{'.$varA.'}`'.$pathB,
                'constraints' => [],
                'expected' => [
                    'regex' => '`^'.$pathA.'\`(?<'.$varA.'>.+?)\`'.$pathB.'$`',
                    'tokens' => [
                        [
                            'name' => $varA,
                            'optional' => false,
                            'regex' => '(?<'.$varA.'>.+?)',
                            'prefix' => $pathA.'\`',
                        ],
                    ],
                ],
            ],
            'with constraint, no default' => [
                'path' => $pathA.'`{'.$varA.'<\d+>}`'.$pathB,
                'constraints' => [],
                'expected' => [
                    'regex' => '`^'.$pathA.'\`(?<'.$varA.'>\d+)\`'.$pathB.'$`',
                    'tokens' => [
                        [
                            'name' => $varA,
                            'optional' => false,
                            'regex' => '(?<'.$varA.'>\d+)',
                            'prefix' => $pathA.'\`',
                        ],
                    ],
                ],
            ],
            'with constraint, with default' => [
                'path' => $pathA.'`{'.$varA.'<\d+>?'.rand().'}`'.$pathB,
                'constraints' => [],
                'expected' => [
                    'regex' => '`^'.$pathA.'\`(?<'.$varA.'>\d+)?\`'.$pathB.'$`',
                    'tokens' => [
                        [
                            'name' => $varA,
                            'optional' => true,
                            'regex' => '(?<'.$varA.'>\d+)',
                            'prefix' => $pathA.'\`',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testCompileSetsParams(): void
    {
        $key     = uniqid('a');
        $default = uniqid();
        $prefix  = uniqid('/');
        $path    = $prefix.'/{'.$key.'?'.$default.'}';
        $fixture = new Route([], $path, uniqid());
        $fixture->compile();

        $actual = $fixture->getParams();

        self::assertEquals([$key => $default], $actual);
    }

    public function testCompileSetsConstraints(): void
    {
        $keyA    = uniqid('a');
        $keyB    = uniqid('b');
        $value   = uniqid('value');
        $default = uniqid();
        $prefix  = uniqid('/');
        $regex   = '[A-Za-z0-9]+';
        $path    = $prefix.'/{'.$keyA.'<'.$regex.'>}';
        $fixture = new Route([], $path, uniqid(), [$keyB => $value]);
        $fixture->compile();

        $actual = $fixture->getConstraints();

        self::assertEquals([$keyB => $value, $keyA => $regex], $actual);
    }
}
