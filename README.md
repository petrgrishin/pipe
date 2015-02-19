# Pipe
[![Travis CI](https://img.shields.io/travis/petrgrishin/pipe/master.svg?style=flat-square)](https://travis-ci.org/petrgrishin/pipe)
[![Coverage Status](https://img.shields.io/coveralls/petrgrishin/pipe.svg?style=flat-square)](https://coveralls.io/r/petrgrishin/pipe?branch=master)

Helper in your project for the integration of middleware

## Example of use Pipe
```php
<?php
use PetrGrishin\Pipe\Pipe;

// Class name
$accessFiltres = [
    AccessFilterMiddleware::class,
];

// Or class name with constructor arguments
$accessFiltres = [
    [AccessFilterMiddleware::class, $paramMiddleware],
];

// Or closure function
$accessFiltres = [
    function (Request $request, Responce $response, Closure $next) {
        return $next($request, $response);
    }
];

// Start the process
Pipe::create($request, $response)
    ->through($accessFiltres)
    ->through($XSSFiltres)
    ->then(function (Request $request, Responce $response) {
        $response->runController($request);
    });
```

## Example middleware
```php
<?php
class AccessFilterMiddleware {
    protected $paramMiddleware;
    
    public function __construct($paramMiddleware = null) {
        $this->paramMiddleware = $paramMiddleware;
    }

    public function __invoke(Request $request, Responce $response, Closure $next) {
        if ($request->isPost()) {
            $response->addError('Post is forbidden');
            return false;
        }
        return $next($request, $response);
    }
}
```
