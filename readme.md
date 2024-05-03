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

## Ein BaseController mit loadView() implementieren
Hier kann die Vererbung der Objekt orientierten Programmierung eingesetzt werden.
Eine abstrakte BaseController-Klasse definiert die Methodensignatur, welche in einer erweiternden BaseController-
Klasse zwingend erforderlich ist.
```txt
AbstractBaseController --> BaseController
```
Die BaseController-Class stellt allen implementierenden Controller-Klassen eine loadView-Methode bereit, die eine
zugehörige View rendered.
```txt
AbstractBaseController --> BaseController --> JobangeboteController
```
Im JobangeboteController ist die loadView-Methode, deren Signatur im AbastractBaseController defineirt und deren
Methodenkörper in der BaseController-Klasse geschrieben wurde, verfügbar.

### Hier eine Checkliste für die nächsten Schritte:

- [ ] Abstrakte BaseController Class definieren
- [ ] BaseController erstellen
- [ ] loadView-Methode definieren
- [ ] JobangeboteController mit index-Methode erstellen
- [ ] Im **View**-Directory ein subdirectory **jobangebote** mit einer index.php erstellen
- [ ] Route erstellen

**src/Controllers/AbstractBaseController.php:**
Definiert Methoden die in der erweiternden Klasse vorhanden sein müssen. Es wird lediglich die Methoden-Signatur 
definiert.

```php
<?php

namespace App\Controllers;

abstract class AbstractBaseController
{
    // Erzwingt die Definition dieser Methode durch die erweiternde Klasse. Funktioniert nicht bei Eigenschaften.
    abstract public function loadView($viewName, $subDir = '', $data = []): void;
}
```

**src/Controllers/BaseController.php:**
Erweitert die AbstractBaseController-Class. Hier wird der Methodenkörper definiert.
```php
<?php

namespace App\Controllers;

use App\Controllers\AbstractBaseController;

class BaseController extends AbstractBaseController
{
    // Path zum View Verzeichnis
    protected string $viewsPath = __DIR__ . '/../Views';

// Methode zum Laden einer View aus einem Unterverzeichnis definieren
    public function loadView($viewName, $subDir = '', $data = []): void
    {
        // Prüfen, ob Unterverzeichnis angegeben wurde und entsprechend den Pfad anpassen
        $path = $this->viewsPath . ($subDir ? '/' . $subDir . '/' : '') . $viewName . '.php';

        // Prüfe ob File existiert
        if (file_exists($path)) {
            // Variablen für die View verfügbar machen
            extract($data);

            // View-File einbinden
            require($path);
        } else {
            // Fehlermeldung, wenn die Datei nicht gefunden wird
            echo "Die View $viewName konnte nicht gefunden werden.";
        }
    }
}
```

**src/Controllers/JobangeboteController.php:**
Der JobangeboteController erweitert den BaseController:
```php
<?php

namespace App\Controller;

use App\Controllers\BaseController;

// JobangeboteController erweitert den BaseController und erbt die loadView-Methode
class JobangeboteController extends BaseController
{
    // Zeigt die index-View von Jobangebote an
    public function index()
    {
//        $this->loadView('index','jobangebote');     // Ok
        parent::loadView('index', 'jobangebote');   // Besser
    }
}
```

**src/Views/jobangebote/index.php:**
```php
<?php

echo "jobangebote - index";
```

**public/index.php:**

Eine Route muss hinzugefügt werden, damit der Controller den Request korrekt verarbeiten kann.
```php
    $route->addRoute('GET', '/jobangebote', 'App\Controllers\JobangeboteController::index');
```

## Implementierung der fluentPDO Library
Für den Datenbankzugriff wird die [fluentpdo Library von envms](https://github.com/envms/fluentpdo?tab=readme-ov-file)
per Composer installiert.

**fluentPDO per Composer installieren:**
```txt
composer require envms/fluentpdo
```

In dem **composer.json**-File muss folgender Code im `require`-Scope hinzugefügt werden, damit die PDO Extension als
Abhängigkeit bekannt gegeben wird:
```json
"ext-pdo": "*"
```
Das **comoser.json**-File mit komplettem Code
```json
{
  "autoload": {
    "psr-4": {
      "App\\": "src"
    }
  },
  "require": {
    "nikic/fast-route": "^1.3",
    "envms/fluentpdo": "^2.2.0",
    "ext-pdo": "*"
  }
}
```

> `"ext-pdo": "*"` Definiert eine Abhängigkeit zur PHP-Erweiterung PDO.

`ext-pdo:` spezifiziert, dass die Abhängigkeit eine PHP-Erweiterung ist. `ext` steht dabei für Extension (Erweiterung) und
`pdo` ist der spezifische Name der Erweiterung.

`*`: Das Sternchen ist eine Platzhalter-Version, die bedeutet, dass jede Version der PDO-Erweiterung akzeptabel ist.

### Eine Datenbankverbindung herstellen

Für eine saubere Implementierung der Datenbank-Klasse und um alle Credentials an einem zentralen Ort zu organisieren,
wird im `project-root`-Verzeichnis eine `config.php` mit Konstanten definiert. Die `example.config.php` kann kopiert
und in `config.php` umbenannt werden. Anschließend werden den Konztanten die Werte für die zugehörigen Credentials
zugewiesen.

```txt
/project-root/
.... config.php
.... example.config.php
```

> Die config.php darf niemals in das VCS.

Die Database-Klasse, die für die Verwaltung der Datenbankverbindung und der Initialisierung von FluentPDO zuständig ist,
sollte in einem Bereich platziert werden, welcher sich nicht im Model, View oder Controller Directory befindet. 
Diese Klasse fungiert als eine Art Dienst (Service), der von
anderen Teilen der Anwendung genutzt wird. Es empfiehlt sich, solch einen Service in einem separaten Ordner zu platzieren,
der Dienste oder Kernfunktionalitäten beherbergt. Zum Beispiel Services, Core oder Config.

```txt
/project-root/
.... /src/
........ /Services/
........ /......../Database.php
```

```php
class Database {
    private $fluent;

    public function __construct() {
        $pdo = new PDO('mysql:host=localhost;dbname=your_db', 'your_username', 'your_password');
        $this->fluent = new \Envms\FluentPDO\Query($pdo);
    }

    public function getFluent() {
        return $this->fluent;
    }
}
```

Für die weitere Implementierung kann wieder eine Funktionalität der objektorientierten Programmierung genutzt werden:
Interface. Ein Interfache ist ein Vertrag, der von allen implementierenden Klassen erfüllt werden muss. Es wird nur die
Signatur der Methoden definiert.

Das **src/Models/ModelInterface.php**
```php
<?php

namespace App\Models;

// Interface für CRUD-Operationen für Model-Klassen
interface ModelInterface
{
    /**
     *  Holt alle Einträge aus einer DB-Tabelle
     *
     * @return mixed
     */
    public function findAll(): mixed;

    /**
     * Holt einen spezifischen Datensatz anhand seiner ID aus einer DB-Tabelle.
     *
     * @param $id
     * @return mixed
     */
    public function findById($id): mixed;


    /**
     * Erstellt einen neuen Datensatz in der DB-Tabelle.
     *
     * @param $data
     * @return mixed
     */
    public function create($data): mixed;


    /**
     * Aktualisiert einen bestehenden Datensatz, identifiziert per ID, in der DB-Tabelle.
     *
     * @param $id
     * @param $data
     * @return mixed
     */
    public function update($id, $data): mixed;


    /**
     * Löscht einen Datensatz aus der DB-Tabelle anhand seiner ID.
     *
     * @param $id
     * @return mixed
     */
    public function delete($id): mixed;
}
```

Dieses Interface wird von dem **src/Models/JobangebotModel.php** implementiert:
In einer privaten Variablen wird ein fluentpdo-Objekt, (das PDO hat die Verbindung zur DB mit unseren Credentials)
durch den Constructor instanziiert. In dem Interface wurden die Signaturen der zu implementierenden Methoden definiert,
in der implementierenden Klasse werden die Methodenkörper codiert.
```php
<?php

namespace App\Models;

use App\Services\Database;
use Envms\FluentPDO\Query;
use PDOStatement;

class JobangebotModel implements ModelInterface
{
    private Query $db;

    public function __construct()
    {
        $this->db = (new Database())->getFluent();
    }

    public function findAll(): bool|array
    {
        return $this->db->from('jobangebote')->fetchAll();
    }

    public function findById($id): mixed
    {
        return $this->db->from('jobangebote')->where('id', $id)->fetch();
    }

    public function create($data): bool|int
    {
        return $this->db->insertInto('jobangebote', $data)->execute();
    }

    public function update($id, $data): bool|int|PDOStatement
    {
        return $this->db->update('jobangebote', $data, $id)->execute();
    }

    public function delete($id): bool
    {
        return $this->db->deleteFrom('jobangebote')->where('id', $id)->execute();
    }
}
```

In dem **src/Controllers/JobangeboteController.php** wird
die Index-Methode refactored, indem das Model alle Daten aus der jobangebote-Tabelle anfragt und an die View als 
weiteren Parameter übergibt.
```php
<?php

namespace App\Controllers;

use App\Models\JobangebotModel;

class JobangeboteController extends BaseController
{
    private JobangebotModel $model;

    public function __construct()
    {
        $this->model = new JobangebotModel();
    }

    /**
     * Zeigt die index-View von joangebote, mit allen vorhandenen Einträgen aus der jobangebote-Tabelle, an.
     *
     * @return void
     */
    public function index(): void
    {
        // Holt alle Einträge aus der jobangebote Tabelle.
        $jobs = $this->model->findAll();

//        $this->loadView('index','jobangebote');     // Ok

        // Lädt die index-View aus Views/jobangebote und übergibt $jobs als weiteren Parameter.
        parent::loadView('index', 'jobangebote', $jobs);   // Besser
    }
}
```

In **src/Views/jobangebote/index.php** wird überprüft, ob der vom Controller übergebende Parameter (das Array) nicht
leer ist. Falls leer, wird eine Information ausgegeben, wenn es nicht leer ist, kann über das Array iteriert werden und die angefragten Daten dynamisch generiert
werden.

Die Funktion **htmlspecialchars()** konvertiert bestimmte Zeichen in HTML-Entitäten. Dadurch können diese keinen Schaden
anrichten.
```php
<?php

echo "jobangebote - index";

//echo "<pre>";
//print_r($data);
//echo "</pre>";


if(is_array($data)) {
    foreach ($data as $job) {
        echo "<p>Titel:" . htmlspecialchars($job['jobtitel']) . "</p>";
    }
} else {
    echo "<p>Aktuell keine Jobs vorhanden </p>";
}
```