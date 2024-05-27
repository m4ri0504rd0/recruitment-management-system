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
        parent::loadView('index', 'jobangebote', ['jobs' => $jobs]);   // Besser
    }
}
```

Im **src/Views/jobangebote/index.php** wird überprüft, ob der vom Controller übergebene Parameter (ein Array) nicht leer 
ist. Wenn das Array leer ist, wird eine entsprechende Information angezeigt. Ist das Array hingegen nicht leer, kann 
darüber iteriert werden, um die angeforderten Daten dynamisch zu generieren.

Die Funktion **htmlspecialchars()** konvertiert bestimmte Zeichen in HTML-Entitäten, um die Sicherheit der Anwendung zu 
gewährleisten. Anschließend werden Links definiert, die Details zu einem Eintrag anzeigen, sowie zum Bearbeiten und 
Löschen des Eintrags verwendet werden können. Abschließend wird ein Link zum Erstellen neuer Jobangebote implementiert

```php
if (is_array($jobs)) {
    foreach ($jobs as $job) {
        echo "<p>Titel: " . htmlspecialchars($job['jobtitel']) . "</p>";
        echo '<a href="/jobangebote/' . htmlspecialchars($job['id']) . '">Details</a> ';
        echo '<a href="/jobangebote/' . htmlspecialchars($job['id']) . '/edit">Bearbeiten</a> ';
        echo '<a href="/jobangebote/' . htmlspecialchars($job['id']) . '/delete">Löschen</a>';
    }
} else {
    echo "<p>Aktuell keine Jobs vorhanden </p>";
}

echo '<hr> <a href="/jobangebote/create">Create</a>';
```

Der JobangebotController verfüt uber eine showView Methode, die einen spezifischen Eintrag aus der Datenbank anhand
seiner ID findet und an die show-View übergibt.
```php
public function showView(int $id): void
{
    $job = $this->model->findById($id);

    parent::loadView('show', 'jobangebote', ['job' => $job]);
}
```

Die **src/Views/jobangebote/show.php** zeigt die Details zu dem Eintrag an. Auch hier wird der Fall behandelt, falls
keine Daten angezeigt werden können.
```php
<?php

echo "jobangebote - show";

//echo "<pre>";
//print_r($job);
//echo "</pre>";

?>

<h1>Jobangebot details</h1>
    <?php if ($job): ?>
        <p><strong>Titel:</strong> <?php echo htmlspecialchars($job['jobtitel']); ?></p>
        <p><strong>Beschreibung:</strong> <?php echo htmlspecialchars($job['beschreibung']); ?></p>
    <?php else: ?>
        <p>Jobangebot nicht gefunden.</p>
    <?php endif; ?>
```

Die editView Methode aus dem **JobangeboteController** findet einen Eintrag aus der Datenbank mithilfe der findById
Methode und übergibt dein Eintrag an die create Methode. Dies setzt die DRY Prinzipien um.  

```php
    public function editView(int $id): void
    {
        $job = $this->model->findById($id);
        $this->loadView('create', 'jobangebote', ['data' => $job]);
    }
```

Das **src/Views/jobangebote/create.php**-File dient zur Erstellung und Bearbeitung von Jobangeboten und implementiert 
das DRY-Prinzip (Don't Repeat Yourself).

#### DRY-Prinzip
Durch die dynamische Generierung von Formular-Titel, Aktions-URL und Formularfeldern wird wiederholter Code vermieden, was das Formular flexibler und leichter wartbar macht

#### Formulartitel und Aktions-URL
Der Titel des Formulars sowie die Aktions-URL werden dynamisch generiert, basierend auf dem Vorhandensein einer id im 
übergebenen $data-Array. Wenn eine id vorhanden ist, wird der Titel auf "Jobangebot bearbeiten" gesetzt und das Formular
sendet die Daten an /jobangebote/update. Wenn keine id vorhanden ist, lautet der Titel "Jobangebot erstellen" und das
Formular sendet die Daten an /jobangebote/create.

#### Verstecktes Feld
Wenn eine id vorhanden ist, wird ein verstecktes Eingabefeld mit der id hinzugefügt, um die Identität des zu
bearbeitenden Jobangebots zu übermitteln.

#### Formularfelder mit Fehlerbehandlung
Für jedes Formularfeld (z.B. abteilung_id, jobtitel, beschreibung) wird überprüft, ob ein Wert im $data-Array vorhanden ist. Falls vorhanden, wird dieser Wert als Standardwert für das entsprechende Eingabefeld gesetzt, um die Formulardaten bei einem Fehler nicht zu verlieren (Form Data Persistence).
Zudem wird für jedes Feld geprüft, ob im $errors-Array ein Fehler vorhanden ist. Falls ja, wird eine Fehlermeldung neben dem entsprechenden Feld angezeigt.

#### Submit-Button
Der Text des Submit-Buttons ändert sich dynamisch je nachdem, ob eine id vorhanden ist. Für die Bearbeitung wird "Aktualisieren" angezeigt, für die Erstellung "Erstellen".
```php
<?php

echo "jobangebote - create";

?>

<h1><?php echo isset($data['id']) ? 'Jobangebot bearbeiten' : 'Jobangebot erstellen'; ?></h1>

<!--<form action="/jobangebote/create" method="post">-->
<form action="/jobangebote/<?php echo isset($data['id']) ? 'update' : 'create'; ?>" method="post">

    <!-- Hidden field -->
    <?php if (isset($data['id'])): ?>
        <input type="hidden"
               name="id"
               value="<?php echo htmlspecialchars($data['id']); ?>"
        >
    <?php endif; ?>

    <!--  NUMBER  with Form Data Persistence -->
    <div class="form-group">
        <label for="abteilung_id">Abteilung_id:</label>
        <input type="number"
               id="abteilung_id"
               name="abteilung_id"
               value="<?php echo htmlspecialchars($data['abteilung_id'] ?? ''); ?>"
        />


        <!-- Error msg -->
        <?php if (isset($errors['abteilung_id'])): ?>
            <span id="error-abteilung_id" style="color: red;">
                <?php echo htmlspecialchars($errors['abteilung_id']); ?>
            </span>
        <?php endif; ?>
    </div>

    <!--  TEXT with Form Data Persistence  -->
    <div class="form-group">
        <label for="jobtitel">Jobtitel:</label>
        <input type="text"
               id="jobtitel"
               name="jobtitel"
               value="<?php echo htmlspecialchars($data['jobtitel'] ?? ''); ?>"
        />

        <!-- Error msg -->
        <?php if (isset($errors['jobtitel'])): ?>
            <span id="error-jobtitel" style="color: red;">
                <?php echo htmlspecialchars($errors['jobtitel']); ?>
            </span>
        <?php endif; ?>
    </div>


    <!--  TEXTAREA  with Form Data Persistence -->
    <div class="form-group">
        <label for="beschreibung">Beschreibung:</label>
        <textarea id="beschreibung"
                  name="beschreibung"
                  rows="4"
                  cols="50">

            <?php echo htmlspecialchars($data['beschreibung'] ?? ''); ?>

        </textarea>

        <!-- Error msg -->
        <?php if (isset($errors['beschreibung'])): ?>
            <span id="error-beschreibung" style="color: red;">
                <?php echo htmlspecialchars($errors['beschreibung']); ?>
            </span>
        <?php endif; ?>
    </div>

    <!--  SUBMIT  -->
    <button type="submit">
        <?php echo isset($data['id']) ? 'Aktualisieren' : 'Erstellen'; ?>
    </button>

</form>
```

Die Methode **create im JobangeboteController** behandelt sowohl GET- als auch POST-Anfragen zur Erstellung eines neuen Jobangebots. Hier ist eine detaillierte Beschreibung der Abläufe:
POST-Request

#### Überprüfung der Anfragemethode:
Die Methode überprüft zuerst, ob die Anfrage eine POST-Anfrage ist.

#### Überprüfung und Verarbeitung der POST-Daten:
Wenn POST-Daten vorhanden sind, werden sie verarbeitet.

#### Sanitizing der Eingaben:
Die Benutzereingaben in $_POST werden durch die Methode sanitizeInput bereinigt, um mögliche schädliche Daten zu entfernen.

#### Validierung der Eingaben:
Die bereinigten Daten werden mittels der validate-Methode validiert.
Die validate-Methode überprüft, ob die Eingaben den gewünschten Kriterien entsprechen und gibt eventuell Fehler zurück.

#### Fehlerfreie Eingaben:
**Wenn keine Validierungsfehler vorhanden sind:**
Die Daten werden in die Datenbank geschrieben, indem die create-Methode des Modells aufgerufen wird.
Nach dem Speichern der Daten wird die index-Methode des Controllers aufgerufen, um die Übersicht der Jobangebote anzuzeigen.

#### Fehlerhafte Eingaben:
**Wenn Validierungsfehler vorliegen:**
Die Fehler sowie die eingegebenen Daten werden an die View weitergegeben, um dem Benutzer die Möglichkeit zu geben, die Fehler zu korrigieren. Dies geschieht durch Aufruf von parent::loadView mit den entsprechenden Parametern.

#### GET-Request

**Anzeigen des Formulars:**
Wenn die Anfrage keine POST-Anfrage ist (also eine GET-Anfrage), wird einfach die View create geladen, die das Formular zur Erstellung eines neuen Jobangebots anzeigt.

```php
public function create(): void
{

    // POST-Request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (isset($_POST)) {

//                echo "<pre>";
//                print_r($_POST);
//                echo "</pre>";

            // Sanitize (Bereinigen) der Benutzereingaben
            $data = $this->sanitizeInput($_POST);

            // Serverseitige Validierung der Felder
            $errors = $this->validate($data);

            // Wenn keine Validierungsfehler
            if (empty($errors)) {
                // Schreibe in DB
                $this->model->create($data);
                // Rufe von diesem Controller die index-Methode auf
                $this->index();
            } else {
                // Fehler an die View weitergeben oder Fehlerbehandlung durchführen
                parent::loadView('create', 'jobangebote', ['data' => $data, 'errors' => $errors]);
            }
        }

    }
    else
    // GET-Request
    {
        parent::loadView('create', 'jobangebote');
    }
}
```

In der create und update Methode werden die Hilfsfunktionen sanitizeInput sowie validate aufgerufen.

Die **sanitizeInput-Methode** dient dazu, alle Eingaben in einem Array zu bereinigen und sicherzustellen, dass sie keine
schädlichen Inhalte enthalten. Hier ist eine detaillierte Beschreibung der Funktionsweise:

Funktionsweise

#### Initialisierung:
Ein leeres Array $sanitizedData wird erstellt, um die bereinigten Daten zu speichern.

#### Durchlauf durch die Eingabedaten:
Die Methode iteriert durch jedes Element des übergebenen Arrays $data.

#### Bereinigung der Eingabewerte:
Für jeden Schlüssel-Wert-Paar ($key => $value) im Array:
- Trimmen: Entfernt führende und nachfolgende Leerzeichen im Wert.
- Konvertierung in HTML-Entitäten: htmlspecialchars konvertiert spezielle Zeichen in HTML-Entitäten, um sicherzustellen, dass die Daten sicher in HTML kontexten verwendet werden können. Dies verhindert Cross-Site Scripting (XSS)-Angriffe.
- Die Option ENT_QUOTES stellt sicher, dass sowohl einfache als auch doppelte Anführungszeichen konvertiert werden.
- Der Zeichensatz UTF-8 wird spezifiziert, um sicherzustellen, dass die Konvertierung korrekt für mehrsprachige Inhalte funktioniert.

#### Speichern der bereinigten Werte:
Der bereinigte Wert wird in das Array $sanitizedData unter demselben Schlüssel gespeichert.

#### Rückgabe der bereinigten Daten:
Das Array $sanitizedData, das nun alle bereinigten Eingabewerte enthält, wird zurückgegeben.

```php
private function sanitizeInput(array $data): array
{
    $sanitizedData = [];
    foreach ($data as $key => $value) {
        $sanitizedData[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
    return $sanitizedData;
}
```

Diese **validate-Methode** überprüft die Gültigkeit der Benutzereingaben und gibt ein Array mit Fehlernachrichten zurück, falls Eingaben nicht den definierten Kriterien entsprechen. Hier ist eine detaillierte Beschreibung der Funktionsweise:
Funktionsweise

#### Initialisierung:
Ein Array $fields enthält die Namen der Felder, die validiert werden sollen: abteilung_id, jobtitel, und beschreibung.
Ein leeres Array $errors wird erstellt, um eventuelle Validierungsfehler zu speichern.

#### Durchlauf durch die Felder:
Die Methode iteriert durch jedes Element im Array $fields.

#### Überprüfung auf leere Felder:
Für jedes Feld wird überprüft, ob es leer ist (empty($data[$field])).
Wenn ein Feld leer ist, wird eine entsprechende Fehlermeldung in $errors gespeichert. Die Fehlermeldung wird dabei dynamisch erstellt, indem der Feldname mit einem Großbuchstaben beginnt (ucfirst($field)).

#### Feldspezifische Validierung:
Wenn das Feld nicht leer ist, wird eine feldspezifische Validierung durchgeführt:
- abteilung_id: Überprüfung, ob der Wert eine Zahl ist (is_numeric($data[$field])). Falls nicht, wird eine Fehlermeldung gespeichert.
- jobtitel: Überprüfung, ob der Wert mindestens 5 Zeichen lang ist (strlen($data[$field]) < 5). Falls nicht, wird eine Fehlermeldung gespeichert.
- beschreibung: Überprüfung, ob der Wert mindestens 10 Zeichen lang ist (strlen($data[$field]) < 10). Falls nicht, wird eine Fehlermeldung gespeichert.

#### Rückgabe der Fehler:
Das Array $errors, das alle gefundenen Validierungsfehler enthält, wird zurückgegeben.

```php
private function validate(array $data): array
{
    $fields = ['abteilung_id', 'jobtitel', 'beschreibung'];
    $errors = [];

    foreach ($fields as $field)
    {
        // Wenn ein Feld leer ist
        if (empty($data[$field]))
        {
            $errors[$field] = ucfirst($field) . ' ist erforderlich.';
        } else
        {
            switch ($field)
            {
                case 'abteilung_id':
                    if (!is_numeric($data[$field]))
                    {
                        $errors[$field] = 'Abteilung ID muss eine Zahl sein.';
                    }
                    break;
                case 'jobtitel':
                    if (strlen($data[$field]) < 5)
                    {
                        $errors[$field] = 'Jobtitel muss mindestens 5 Zeichen lang sein.';
                    }
                    break;
                case 'beschreibung':
                    if (strlen($data[$field]) < 10)
                    {
                        $errors[$field] = 'Beschreibung muss mindestens 10 Zeichen lang sein.';
                    }
                    break;
            }
        }
    }

    return $errors;
}
```
Diese **update-Methode im JobangeboteController** behandelt die Aktualisierung eines bestehenden Jobangebots. Hier ist eine detaillierte Beschreibung der Abläufe:
Funktionsweise

#### Überprüfung der ID:
Die Methode überprüft, ob eine id im POST-Request vorhanden ist ($id = $_POST['id'] ?? null).
        Wenn keine id vorhanden ist ($id === null), wird ein HTTP-Statuscode 400 (Bad Request) zurückgegeben und eine Fehlermeldung "Invalid ID" ausgegeben. Die Methode wird dann beendet (return).

#### Sanitizing der Eingaben:
Die POST-Daten werden mit der Methode sanitizeInput bereinigt, um schädliche Inhalte zu entfernen.

#### Validierung der Eingaben:
Die bereinigten Daten werden mit der validate-Methode validiert. Es werden die Felder abteilung_id, jobtitel und beschreibung überprüft.

#### Fehlerfreie Eingaben:
**Wenn keine Validierungsfehler vorliegen (empty($errors)):**
- Die Daten werden in der Datenbank aktualisiert, indem die update-Methode des Modells aufgerufen wird.
- Nach dem Aktualisieren wird die index-Methode des Controllers aufgerufen, um die Übersicht der Jobangebote anzuzeigen.

#### Fehlerhafte Eingaben:
**Wenn Validierungsfehler vorliegen:**
- Die Fehler und die eingegebenen Daten werden an die View weitergegeben, um dem Benutzer die Möglichkeit zu geben, die Fehler zu korrigieren. Dies geschieht durch Aufruf von loadView mit den entsprechenden Parametern.

```php
    public function update(): void
    {
        $id = $_POST['id'] ?? null;

        if ($id === null) {
            http_response_code(400);
            echo "Invalid ID";
            return;
        }

        // Sanitize (Bereinigen) der Benutzereingaben
        $data = $this->sanitizeInput($_POST);

        // Validierung
        $errors = $this->validate($data, ['abteilung_id', 'jobtitel', 'beschreibung']);

        if (empty($errors)) {
            $this->model->update($id, $data);
            $this->index();
        } else {
            $this->loadView('create', 'jobangebote', ['data' => $data, 'errors' => $errors]);
        }
    }
```

Die deleteView-Methode im Controller ist dafür verantwortlich, die View für das Löschen eines bestimmten Jobangebots anzuzeigen. Hier ist eine detaillierte Beschreibung der Abläufe:
Funktionsweise

#### Finden des Jobangebots:
- Die Methode ruft findById($id) vom Modell auf, um das Jobangebot mit der gegebenen ID aus der Datenbank zu finden.
- Das gefundene Jobangebot wird in der Variable $job gespeichert.

#### Laden der Delete-View:
- Die Methode ruft loadView auf, um die delete-View im Kontext von jobangebote zu laden.
- Die View wird mit den Daten des gefundenen Jobangebots (gespeichert in $job) aufgerufen. Diese Daten werden als assoziatives Array mit dem Schlüssel 'data' übergeben.

```php
public function deleteView(int $id)
{
    $job = $this->model->findById($id);
    $this->loadView('delete', 'jobangebote', ['data' => $job]);
}
```

Die delete-Methode im Controller behandelt die Löschanfrage eines bestimmten Jobangebots. Hier ist eine detaillierte Beschreibung der Abläufe:
Funktionsweise

#### Überprüfung der ID:
- Die Methode überprüft, ob eine id im POST-Request vorhanden ist ($id = $_POST['id'] ?? null).
- Wenn keine id vorhanden ist ($id === null), wird ein HTTP-Statuscode 400 (Bad Request) zurückgegeben und eine Fehlermeldung "Invalid ID" ausgegeben. Die Methode wird dann beendet (return).

#### Löschen des Jobangebots:
- Wenn eine gültige id vorhanden ist, wird die Methode delete($id) vom Modell aufgerufen, um das Jobangebot mit der gegebenen ID aus der Datenbank zu löschen.

#### Rückkehr zur Übersicht:
- Nach dem Löschen wird die index-Methode des Controllers aufgerufen, um die Übersicht der verbleibenden Jobangebote anzuzeigen.

```php
public function delete(): void
{
    $id = $_POST['id'] ?? null;

    if ($id === null) {
        http_response_code(400);
        echo "Invalid ID";
        return;
    }

    $this->model->delete($id);
    $this->index();
}
```