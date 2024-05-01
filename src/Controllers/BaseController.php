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