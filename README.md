# Bitty Router

[![Build Status](https://travis-ci.org/bittyphp/router.svg?branch=master)](https://travis-ci.org/bittyphp/router)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/df88477403554bd9aceef89d761644f3)](https://www.codacy.com/app/bittyphp/router)
[![PHPStan Enabled](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)
[![Mutation Score](https://badge.stryker-mutator.io/github.com/bittyphp/router/master)](https://infection.github.io)
[![Total Downloads](https://poser.pugx.org/bittyphp/router/downloads)](https://packagist.org/packages/bittyphp/router)
[![License](https://poser.pugx.org/bittyphp/router/license)](https://packagist.org/packages/bittyphp/router)

Bitty's router is a [PSR-15](https://www.php-fig.org/psr/psr-15/) middleware component and supports [PSR-7](http://www.php-fig.org/psr/psr-7/) HTTP message interfaces. When a route is called, it is passed an instance of `Psr\Http\Message\ServerRequestInterface` and must return an instance of `Psr\Http\Message\ResponseInterface`.

## Installation

It's best to install using [Composer](https://getcomposer.org/).

```sh
$ composer require bittyphp/router
```

### Setup

The router itself is merely a wrapper around a few services:

1. **The route collection.** This holds all the routes to be shared between other router services.
2. **The route matcher.** Matches routes against an HTTP request.
3. **URI generator.** Generates a URI for a given route.

```php
<?php

use Bitty\Router\RouteCollection;
use Bitty\Router\RouteMatcher;
use Bitty\Router\Router;
use Bitty\Router\UriGenerator;

$domain    = 'http://example.com/';
$routes    = new RouteCollection();
$matcher   = new RouteMatcher($routes);
$generator = new UriGenerator($routes, $domain);

$router = new Router($routes, $matcher, $generator);
```

## Adding a Route

Routes can be customized pretty well. Bitty supports the following options:

1. **Multiple HTTP request methods.** Have a single route match against one or multiple request methods.
2. **Resource pattern matching.** Specify pattern constraints for routes to match on resource IDs, blog slugs, specific file patterns, or anything else you can think of. The matching parameters are automatically passed into the route callback as an associative array.
3. **Multiple controller callback types.** Controllers can be defined using an anonymous function, invokable class, or an actual controller file with an action method to call.
4. **Named routes.** Routes can be named to make them more easy to reference. Of course, unnamed routes are supported, too.

### Basic Usage

```php
<?php

use Bitty\Http\Response;
use Bitty\Router\Router;
use Psr\Http\Message\ServerRequestInterface;

$router = new Router(...);

$router->add('GET', '/resource/path', function (ServerRequestInterface $request) {
    return new Response('Hello, world!');
});
```

### Multiple Request Methods

If you want, you can use the same route for multiple request methods. For the first parameter, simply pass in an array listing all the methods you want. Then you can use the request object to determine what method was used, if needed.

```php
<?php

use Bitty\Http\Response;
use Bitty\Router\Router;
use Psr\Http\Message\ServerRequestInterface;

$router = new Router(...);

$router->add(['GET', 'POST'], '/resource/path', function (ServerRequestInterface $request) {
    if ($request->getMethod() === 'POST') {
        return new Response('You did a POST');
    }

    return new Response('You did a GET');
});
```

### Resource Pattern Matching

You can define patterns to create routes that automatically extract variables from the route path, e.g. getting a product's ID or the slug for a blog entry. Variables are defined by an opening curly bracket (`{`), any alpha-numeric characters or underscore (`A-Z`, `a-z`, `0-9`, `_`), and a closing curly bracket (`}`).

```php
<?php

use Bitty\Http\Response;
use Bitty\Router\Router;
use Psr\Http\Message\ServerRequestInterface;

$router = new Router(...);

$router->add(
    'GET',

    // Define the variables by placing curly brackets around a string.
    // You can define as many variables as needed.
    '/products/{id}',

    // Our callback can access the variable from the Request object.
    function (ServerRequestInterface $request) {
        return new Response('You requested product '.$request->getAttribute('id'));
    }
);
```

#### Resource Pattern Constraints

Routes that contain patterns can optionally specify the constraints to fulfill those patterns. Constraints are specified by passing in an additional array. For example, maybe you want to ensure a product ID only contains digits.

```php
<?php

use Bitty\Http\Response;
use Bitty\Router\Router;
use Psr\Http\Message\ServerRequestInterface;

$router = new Router(...);

$router->add(
    'GET',

    // Define the variables.
    '/products/{id}',

    // Our callback can access the variable from the Request object.
    function (ServerRequestInterface $request) {
        return new Response('You requested product '.$request->getAttribute('id'));
    },

    // Define the constraints. In this case, only look for digits.
    ['id' => '\d+']
);
```

The above example essentially combines the path and the constraints to create a regex pattern of `/products/(\d+)`. It will match on requests for `/products/123`, but will not match on `/products/ABC123`.

Optionally, you can specify the constraints directly in the pattern. This has the exact same effect as the previous example, but might be more difficult to read for complex routes. After the variable name, type a regex value surrounded by angle brackets (`<`, `>`).
```php
<?php

use Bitty\Http\Response;
use Bitty\Router\Router;
use Psr\Http\Message\ServerRequestInterface;

$router = new Router(...);

$router->add(
    'GET',

    // Define the variables and constraints.
    '/products/{id<\d+>}',

    // Our callback can access the variable from the Request object.
    function (ServerRequestInterface $request) {
        return new Response('You requested product '.$request->getAttribute('id'));
    }
);
```

#### Optional Resource Patterns

Sometimes parameters aren't necessary to fulfill the request and can default to a set value. You can specify a parameter as optional by adding a `?` after the variable name. Additionally, you can enter a value after the `?` to use as the default. If no value is given, it uses to `null`.

```php
<?php

use Bitty\Http\Response;
use Bitty\Router\Router;
use Psr\Http\Message\ServerRequestInterface;

$router = new Router(...);

$router->add(
    'GET',

    // Match against multiple optional parameters.
    '/blog/{year<\d{4}>?2019}/{month?}/{day?}',

    // Our callback.
    function (ServerRequestInterface $request) {
        $year = $request->getAttribute('year');
        $month = $request->getAttribute('month');
        $day = $request->getAttribute('day');

        return new Response(...);
    },

    // Constraints.
    [
        'month' => '\d+',
        'day' => '\d+',
    ]
);
```

The above example allows us to navigate to `/blog`, `/blog/2018`, `/blog/2014/10`, or `/blog/2007/07/17`. Notice that the `/` separators also become optional. Currently, only `.` and `/` are seen as separators.

### Multiple Callback Types

To be as flexible as possible, Bitty supports using a couple different styles of callbacks. You can use an anonymous function or pass in a string. If using a string, it should reference a class or container object to build. Optionally, the string can also include a method to call. If no method is given, it assumes `__invoke` should be used. The class and method should be separated with a colon (`:`), e.g. `Acme\\SomeClass:someMethod`.

Lets create a route that triggers an action in a controller class. First, we make the controller.

```php
<?php

namespace Acme\Controller;

use Bitty\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExampleController
{
    public function test(ServerRequestInterface $request): ResponseInterface
    {
        return new Response('Hey, the controller worked!');
    }
}
```

Now we create a route and point it to the class we made and tell it what method to call. When someone visits the route, the `ExampleController` will be built and then the `test` method will be called. If `ExampleController` is defined in the container, it will be loaded from the container. If a container entry isn't found, the class constructor will be passed an instance of `Psr\Container\ContainerInterface`.

```php
<?php

use Acme\Controller\ExampleController;
use Bitty\Router\Router;

$router = new Router(...);

$router->add('GET', '/resource/path', ExampleController::class.':test');
```

When a `Closure` is used instead of a class, the container gets bound to the `$this` variable.

```php
<?php

use Bitty\Http\Response;
use Bitty\Router\Router;

$router = new Router(...);

$router->add('GET', '/resource/path', function () {
    $myService = $this->get('some.container.service');

    // ...

    return new Response(...);
});
```

### Named Routes

Named routes are handy if you know you'll be referencing them later, like by building a URI that points to it. You can specify the name for a route by passing in a fifth parameter (remember, the fourth parameter is an array of constraints).

```php
<?php

use Acme\Controller\ExampleController;
use Bitty\Router\Router;

$router = new Router(...);

// Set it directly.
$router->add('GET', '/foo', ExampleController::class, [], 'foo_route');

// Set it after.
$router->add('GET', '/foo', ExampleController::class)->setName('foo_route');
```

## Checking For a Route

You can check if a named route exists using the `has()` method.

```php
<?php

use Bitty\Router\Router;

$router = new Router(...);

if ($router->has('some.route')) {
    echo 'it exists!';
}
```

## Fetching a Route

There are two ways to fetch a route: 1) You can get the route by name, or 2) You can find the route using the HTTP request.

### By Name

You can use the `get()` method to fetch any named route. This will return a route object, which you can then modify or use as you desire. However, if the route doesn't exist a `Bitty\Router\Exception\NotFoundException` will be thrown.

```php
<?php

use Bitty\Router\Router;

$router = new Router(...);

// Get a route
$route = $router->get('some.route');

// Change it to accept both GET and POST requests
$route->setMethods(['GET', 'POST']);
```

### By Request

You can use the `match()` method to fetch a route based on a request. This allows you to find both named or unnamed routes. Similar to `get()`, if the route doesn't exist a `Bitty\Router\Exception\NotFoundException` will be thrown. If it finds a route, the route object will be returned.

```php
<?php

use Bitty\Router\Router;
use Psr\Http\Message\ServerRequestInterface;

$router = new Router(...);

/** @var ServerRequestInterface */
$request = ...;

// Find a matching route
$route = $router->match($request);
```

## Generating a URI

At some point, you likely need to create a URI to reference for a particular route. The router can use the route data, including filling in extra parameters, to generate such a URI for you. By default, the URI it returns only includes the path relative to root.

```php
<?php

use Bitty\Router\Router;

$router = new Router(...);

$router->add(
    'GET',
    '/products/{id}',
    ExampleController::class,
    ['id' => '\d+'],
    'view_product'
);

$uri = $router->generateUri('view_product', ['id' => 123]);
// Outputs: /products/123
```

To return an absolute URI, you'll need to tell the router you want the absolute URI. By default, it returns the absolute path only.

```php
<?php

use Bitty\Router\Router;
use Bitty\Router\UriGeneratorInterface;

$router = new Router(...);

$router->add(
    'GET',
    '/products/{id}',
    ExampleController::class,
    ['id' => '\d+'],
    'view_product'
);

$uri = $router->generateUri(
    'view_product',
    ['id' => 123],
    UriGeneratorInterface::ABSOLUTE_URI
);
// Outputs: http://example.com/products/123
```

### Route Parameters

If you fail to pass in required route parameters, the `UriGenerator` will throw a `Bitty\Router\Exception\UriGeneratorException` exception. Any extra parameters you
pass will be added to the query string.

```php
<?php

use Bitty\Router\Router;

$router = new Router(...);

$router->add(
    'GET',
    '/products/{id}',
    ExampleController::class,
    ['id' => '\d+'],
    'view_product'
);

$uri = $router->generateUri('view_product', ['id' => 123, 'foo' => 'bar']);
// Outputs: /products/123?foo=bar
```

## Using as Middleware

The middleware layer is made of two parts: the route handler and the middleware wrapper.

### Route Handler

The `RouteHandler` is responsible for determining which action to take for a desired route and returning the response from that action. It can be passed into any service that accepts `Psr\Http\Server\RequestHandlerInterface`.

```php
<?php

use Bitty\Router\CallbackBuilder;
use Bitty\Router\RouteHandler;
use Bitty\Router\Router;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/** @var ContainerInterface */
$container = ...;

$handler = new RouteHandler(
    new Router(...),
    new CallbackBuilder($container)
);

/** @var ServerRequestInterface */
$request = ...;

/** @var ResponseInterface */
$response = $handler->handle($request);

```

### Routing Middleware

The router can be set up as a PSR middleware component to handle HTTP requests. The middleware object can be passed into anything that accepts `Psr\Http\Server\MiddlewareInterface`.

```php
<?php

use Bitty\Router\RouteHandler;
use Bitty\Router\RoutingMiddleware;

$middleware = new RoutingMiddleware(
    new RouteHandler(...)
);

```
