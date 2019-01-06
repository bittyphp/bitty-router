<?php

namespace Bitty\Tests\Router\Stubs;

use Psr\Http\Message\ResponseInterface;

interface InvokableStubInterface
{
    /**
     * Mock invokable.
     */
    public function __invoke(): ResponseInterface;
}
