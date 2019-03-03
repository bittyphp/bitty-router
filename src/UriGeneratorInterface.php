<?php

namespace Bitty\Router;

use Bitty\Router\Exception\NotFoundException;

interface UriGeneratorInterface
{
    /**
     * Indicates to return an absolute URI, a.k.a. a full URL.
     *
     * For example, http://www.example.com/some/path.html
     *
     * @var string
     */
    public const ABSOLUTE_URI = 'absolute-uri';

    /**
     * Indicates to return an absolute path (relative to root).
     *
     * For example, /some/path.html
     *
     * @var string
     */
    public const ABSOLUTE_PATH = 'absolute-path';

    /**
     * Indicates to return a network URI (no scheme prefix).
     *
     * For example, //www.example.com/some/path.html
     *
     * @var string
     */
    public const NETWORK_URI = 'network-uri';

    /**
     * Generates a URI for the given named route.
     *
     * @param string $name
     * @param mixed[] $params
     * @param string $type
     *
     * @return string
     *
     * @throws NotFoundException
     */
    public function generate(
        string $name,
        array $params = [],
        string $type = self::ABSOLUTE_PATH
    ): string;
}
