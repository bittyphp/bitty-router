<?php

namespace Bitty\Router;

use Bitty\Http\Uri;
use Bitty\Router\Exception\UriGeneratorException;
use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteInterface;
use Bitty\Router\UriGeneratorInterface;

class UriGenerator implements UriGeneratorInterface
{
    /**
     * @var RouteCollectionInterface
     */
    private $routes = null;

    /**
     * @var string
     */
    private $domain = null;

    /**
     * @param RouteCollectionInterface $routes
     * @param string $domain
     */
    public function __construct(RouteCollectionInterface $routes, string $domain = '')
    {
        $this->routes = $routes;
        $this->domain = rtrim($domain, '/');
    }

    /**
     * {@inheritDoc}
     */
    public function generate(
        string $name,
        array $params = [],
        string $type = self::ABSOLUTE_PATH
    ): string {
        $route = $this->routes->get($name);
        $path = $this->buildPath($route, $params);

        if ($type === self::ABSOLUTE_URI) {
            $uri = new Uri($this->domain.'/'.ltrim($path, '/'));
        } else {
            $uri = new Uri($path);
        }

        $query = [];
        foreach ($params as $id => $value) {
            $query[] = urlencode(urldecode(strval($id)))
                .'='.urlencode(urldecode($value));
        }

        return $uri->withQuery(implode('&', $query));
    }

    /**
     * Builds the route path.
     *
     * @param RouteInterface $route
     * @param string[] $params
     *
     * @return string
     */
    private function buildPath(RouteInterface $route, array &$params): string
    {
        $path = '';
        $compiled = $route->compile();
        foreach (array_reverse($compiled['tokens']) as $token) {
            if ($token['type'] === 'text') {
                $path = $token['prefix'].$path;

                continue;
            }

            $name = $token['name'];
            $isOptional = $token['optional'];

            if (!$isOptional && empty($params[$name])) {
                throw new UriGeneratorException(
                    sprintf('Parameter "%s" is required.', $name)
                );
            }

            if (!isset($params[$name])) {
                continue;
            }

            $path = $token['prefix'].$params[$name].$path;
            unset($params[$name]);
        }

        return $path;
    }
}
