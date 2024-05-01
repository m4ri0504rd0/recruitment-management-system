# Recruitment Management System
Ein Leitfaden zur Programmierung des Projekts

## Vorbereitung
### Die Projektstruktur wird erstellt
```txt
/project-root/
.... /src/
........ /Controllers/
........ /Models/
........ /Views/
.... /public/
........ /css/
............ style.css
........ /js/
............ script.js
........ index.php
........ .htaccess
....composer.json
.... .htaccess
```

Die **.htaccess**-Files leiten den eingehenden Request zuerst in `project-root/public`, dann zum
`project-root/public/index.php`, dem Haupteinstiegspunkt der Anwendung.

### Installation von Composer
Falls Composer noch nicht installiert ist, lade ihn von [getcomposer.org](getcomposer.org) herunter und folgen den
Installationsanweisungen für dein Betriebssystem.

### Konfiguration von Composer für PSR-4 Autoloading
Der `composer.json` muss mitgeteilt werden, das alle Klassen, die sich im Namespace **App** befinden, im Verzeichnis 
**src/** liegen.

```json
{
    "autoload": {
            "psr-4": {
                "App\\": "src/"
            }
    }
}
```

Im Anschluss muss das `vendor/`-directory sowie die **Autoloader-Skripte** erstellt werden. Dies erfolgt durch folgende
Eingabe im Terminal der IDE.

```bash
composer dump-autoload
```
Erstelle den ersten commit im `main`-Branch. Die weitere implementierung von externen Packages / Libraries sowie Logik erfolgt in dem `development`-Branch. 

## Der erste Controller

**Erweiterung der Projektstruktur:**
```txt
src/Controllers/HomeController.php
```

**src/Controllers/HomeController.php:**
```php
<?php

namespace App\Controllers;

class HomeController
{
    // Gibt das echo-statement zurück wenn ein request an localhost/ gesendet wird. 
    public function index()
    {
        echo 'Hallo von src/Views/Home/index.php';
    }
}
```

**public/index.php:**
```php
<?php
// Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Instanziierung HomeController & call der index-Methode
$controller = new App\Controllers\HomeController();
$controller->index();
```

## Implementierung einer Router-Library
Als Router wird die [FastRoute Library von nikic](https://github.com/nikic/FastRoute) per Composer installiert. Es ist
ratsam, ab diesem Zeitpunkt das Error-Reporting für die Entwicklung zu aktivieren. Dies erfolgt in public/index.php und
direkt nach dem opening-php-tag eingetragen.

**public/index.php:**
```php
error_reporting(E_ALL);
ini_set('display_errors', '1');
```

**public/index.php:**

Die manuelle instanziierung sowie der Methodenaufruf des HomeController kann entfernt werden und anstelle dessen wird
der Router implementiert.
```php
// Dispatcher einrichten
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $route) {
    $route->addRoute('GET', '/', 'App\Controllers\HomeController::index');
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
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        list($class, $method) = explode('::', $handler, 2);
        call_user_func_array([new $class, $method], $vars);
        break;
}
```


