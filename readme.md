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

$controller = new App\Controllers\HomeController();
$controller->index();
```



