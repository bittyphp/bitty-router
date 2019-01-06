<?php

namespace Bitty\Tests\Router;

use Bitty\Router\Exception\UriGeneratorException;
use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteInterface;
use Bitty\Router\UriGenerator;
use Bitty\Router\UriGeneratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UriGeneratorTest extends TestCase
{
    /**
     * @var UriGenerator
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

        $this->fixture = new UriGenerator($this->routes);
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(UriGeneratorInterface::class, $this->fixture);
    }

    public function testGenerateGetsRoute(): void
    {
        $name  = uniqid();
        $route = $this->createConfiguredMock(RouteInterface::class, ['getPath' => '']);

        $this->routes->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($route);

        $this->fixture->generate($name);
    }

    /**
     * @param string $path
     * @param string $domain
     * @param string $name
     * @param array $params
     * @param string $type
     * @param string $expected
     *
     * @dataProvider sampleGenerate
     */
    public function testGenerate(
        string $path,
        string $domain,
        string $name,
        array $params,
        string $type,
        string $expected
    ): void {
        $route = $this->createConfiguredMock(RouteInterface::class, ['getPath' => $path]);
        $this->routes->method('get')->willReturn($route);

        $fixture = new UriGenerator($this->routes, $domain);
        $actual  = $fixture->generate($name, $params, $type);

        self::assertEquals($expected, $actual);
    }

    public function sampleGenerate(): array
    {
        $name   = uniqid('name');
        $pathA  = '/'.uniqid('path');
        $pathB  = uniqid('path');
        $paramA = uniqid('param');
        $paramB = uniqid('param');
        $domain = uniqid('domain');

        return [
            'no params' => [
                'path' => $pathA,
                'domain' => '',
                'name' => $name,
                'params' => [],
                'type' => UriGeneratorInterface::ABSOLUTE_PATH,
                'expected' => $pathA,
            ],
            'one param' => [
                'path' => $pathA.'/{paramA}',
                'domain' => '',
                'name' => $name,
                'params' => ['paramA' => $paramA],
                'type' => UriGeneratorInterface::ABSOLUTE_PATH,
                'expected' => $pathA.'/'.$paramA,
            ],
            'one unknown param' => [
                'path' => $pathA,
                'domain' => '',
                'name' => $name,
                'params' => ['paramA' => $paramA],
                'type' => UriGeneratorInterface::ABSOLUTE_PATH,
                'expected' => $pathA.'?paramA='.$paramA,
            ],
            'multiple params' => [
                'path' => $pathA.'/{paramA}/{paramB}',
                'domain' => '',
                'name' => $name,
                'params' => ['paramA' => $paramA, 'paramB' => $paramB],
                'type' => UriGeneratorInterface::ABSOLUTE_PATH,
                'expected' => $pathA.'/'.$paramA.'/'.$paramB,
            ],
            'multiple unknown params' => [
                'path' => $pathA,
                'domain' => '',
                'name' => $name,
                'params' => ['paramA' => $paramA, 'paramB' => $paramB],
                'type' => UriGeneratorInterface::ABSOLUTE_PATH,
                'expected' => $pathA.'?paramA='.$paramA.'&paramB='.$paramB,
            ],
            'mixed params' => [
                'path' => $pathA.'/{paramA}',
                'domain' => '',
                'name' => $name,
                'params' => ['paramA' => $paramA, 'paramB' => $paramB],
                'type' => UriGeneratorInterface::ABSOLUTE_PATH,
                'expected' => $pathA.'/'.$paramA.'?paramB='.$paramB,
            ],
            'domain with trailing slash' => [
                'path' => $pathB,
                'domain' => $domain.'/',
                'name' => $name,
                'params' => [],
                'type' => UriGeneratorInterface::ABSOLUTE_URI,
                'expected' => '/'.$domain.'/'.$pathB,
            ],
            'path with leading slash' => [
                'path' => $pathA,
                'domain' => $domain,
                'name' => $name,
                'params' => [],
                'type' => UriGeneratorInterface::ABSOLUTE_URI,
                'expected' => '/'.$domain.$pathA,
            ],
            'no slashes' => [
                'path' => $pathB,
                'domain' => $domain,
                'name' => $name,
                'params' => [],
                'type' => UriGeneratorInterface::ABSOLUTE_URI,
                'expected' => '/'.$domain.'/'.$pathB,
            ],
        ];
    }

    public function testGenerateThrowsException(): void
    {
        $path  = uniqid('path').'/{param}';
        $route = $this->createConfiguredMock(RouteInterface::class, ['getPath' => $path]);
        $this->routes->method('get')->willReturn($route);

        $this->expectException(UriGeneratorException::class);
        $this->expectExceptionMessage('Parameter "param" is required.');

        $this->fixture->generate(uniqid());
    }
}
