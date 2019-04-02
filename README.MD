## Requirements

The following versions of PHP are supported.

* PHP 7.1+

## Installation

To install, use composer:

```
php composer.phar require pe/component-cronos-http
```

## Client usage

```php
<?php

namespace PE\Component\Cronos;

use Http\Client\Curl\Client;
use PE\Component\Cronos\Core\Serializer;
use PE\Component\Cronos\HTTP\HTTPClient;
use Zend\Diactoros\RequestFactory;
use Zend\Diactoros\StreamFactory;
use Zend\Diactoros\UriFactory;

// Configure client
$client = new Client();

// Configure cronos client
$http = new HTTPClient(
    'http://example.com',
    $client,
    new Serializer(),
    new UriFactory(),
    new StreamFactory(),
    new RequestFactory()
);

// Then you can send requests
$http->request('ACTION_NAME', ['PARAMS']);// -> decoded response
```

## Server usage

```php
<?php

namespace PE\Component\Cronos;

use PE\Component\Cronos\Core\Serializer;
use PE\Component\Cronos\Core\ServerInterface;
use PE\Component\Cronos\HTTP\HTTPResponder;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\StreamFactory;

/* @var $server ServerInterface */
$server = '...';

// Configure responder
$http = new HTTPResponder(
    $server,
    new Serializer(),
    new StreamFactory(),
    new ResponseFactory()
);

// Handle request
$response = $http->handle(ServerRequestFactory::fromGlobals());

// Send response
(new SapiEmitter())->emit($response);
```
