<?php

namespace Bitty\Router;

use Bitty\Http\Uri;
use Bitty\Router\Exception\UriGeneratorException;
use Bitty\Router\RouteCollectionInterface;
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
        $path  = $route->getPath();

        $requiredParams = $this->getRequiredParams($path);
        foreach ($requiredParams as $param) {
            if (!isset($params[$param])) {
                throw new UriGeneratorException(
                    sprintf('Parameter "%s" is required.', $param)
                );
            }

            $path = str_replace('{'.$param.'}', $params[$param], $path);
            unset($params[$param]);
        }

        if (self::ABSOLUTE_URI === $type) {
            $uri = new Uri($this->domain.'/'.ltrim($path, '/'));
        } else {
            $uri = new Uri($path);
        }

        $query = [];
        foreach ($params as $id => $value) {
            $query[] = urlencode(urldecode($id))
                .'='.urlencode(urldecode($value));
        }

        return $uri->withQuery(implode('&', $query));
    }

    /**
     * Gets the required parameters needed for the path.
     *
     * @param string $path
     *
     * @return string[] Array of required parameter names.
     */
    private function getRequiredParams(string $path): array
    {
        $matches = [];
        preg_match_all('/\{([\w-]+)\}/', $path, $matches);
        array_shift($matches);

        return !empty($matches) ? $matches[0] : [];
    }
}
