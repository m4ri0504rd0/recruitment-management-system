<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Dispatcher einrichten
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $route) {
    $route->addRoute('GET', '/', 'App\Controllers\HomeController::index');
    $route->addRoute('GET', '/jobangebote', 'App\Controllers\JobangeboteController::index');
    $route->addRoute('GET', '/jobangebote/{id:\d+}', 'App\Controllers\JobangeboteController::showView');
    $route->addRoute('GET', '/jobangebote/create', 'App\Controllers\JobangeboteController::create');
    $route->addRoute('POST', '/jobangebote/create', 'App\Controllers\JobangeboteController::create');
    $route->addRoute('GET', '/jobangebote/{id:\d+}/edit', 'App\Controllers\JobangeboteController::editView');
    $route->addRoute('POST', '/jobangebote/update', 'App\Controllers\JobangeboteController::update');
    $route->addRoute('POST', '/jobangebote/delete', 'App\Controllers\JobangeboteController::delete');
    $route->addRoute('GET', '/jobangebote/{id:\d+}/delete', 'App\Controllers\JobangeboteController::deleteView');
    // Weitere Routen ...
});

// HTTP-Methode und URI abrufen
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Unnötige URL-Teile (?) entfernen
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// Routing abgleichen
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // Keine Route gefunden
        http_response_code(404);
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        // Methode nicht erlaubt
        $allowedMethods = $routeInfo[1];
        http_response_code(405);
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        // Route gefunden, Handler ausführen
        $handler = $routeInfo[1]; // TODO: CHECK Potential Object Injection
        $vars = $routeInfo[2]; // TODO: CHECK Potential Object Injection
        list($class, $method) = explode('::', $handler, 2);
        call_user_func_array([new $class, $method], $vars);
        break;
}