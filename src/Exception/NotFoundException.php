<?php

namespace Bitty\Router\Exception;

use Bitty\Router\Exception\RouterException;

class NotFoundException extends RouterException
{
    /**
     * Default message to use.
     *
     * @var string
     */
    protected $message = 'Route not found';
}
