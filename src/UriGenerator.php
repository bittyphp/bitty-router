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
    protected $routes = null;

    /**
     * @var string
     */
    protected $domain = null;

    /**
     * @param RouteCollectionInterface $routes
     * @param string $domain
     */
    public function __construct(RouteCollectionInterface $routes, $domain = '')
    {
        $this->routes = $routes;
        $this->domain = rtrim($domain, '/');
    }

    /**
     * {@inheritDoc}
     */
    public function generate($name, array $params = [], $type = self::ABSOLUTE_PATH)
    {
        $route = $this->routes->get($name);
        $path  = $route->getPath();

        $requiredParams = $this->getRequiredParams($path);
        foreach ($requiredParams as $param) {
            if (!isset($params[$param])) {
                throw new UriGeneratorException(sprintf('Parameter "%s" is required.', $param));
            }

            $path = str_replace('{'.$param.'}', (string) $params[$param], $path);
            unset($params[$param]);
        }

        if (self::ABSOLUTE_URI === $type) {
            $uri = new Uri($this->domain.'/'.ltrim($path, '/'));
        } else {
            $uri = new Uri($path);
        }

        $query = [];
        foreach ($params as $id => $value) {
            $query[] = urlencode(urldecode($id)).'='.urlencode(urldecode($value));
        }

        return (string) $uri->withQuery(implode('&', $query));
    }

    /**
     * Gets the required parameters needed for the path.
     *
     * @param string $path
     *
     * @return string[] Array of required parameter names.
     */
    protected function getRequiredParams($path)
    {
        $matches = [];
        preg_match_all('/\{([\w-]+)\}/', $path, $matches);

        return count($matches) > 1 ? $matches[1] : [];
    }
}
