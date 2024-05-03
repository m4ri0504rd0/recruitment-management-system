<?php

namespace App\Services;

use Envms\FluentPDO\Query;
use PDO;

require_once __DIR__ . '/../config.php';    // Für Zugriff auf die DB credentials

class Database {

    // Query-Objekt
    private Query $fluent;

    /**
     * Der Konstruktor wird bei Erstellung einer Instanz aufgerufen.
     */
    public function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;          // DSN-String für die PDO-Verbindung mit der DB
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);   // Erstellt ein neues PDO-Objekt mit DB-Verbindung
        $this->fluent = new Query($pdo);                                // Initialisiert das FluentPDO Query-Objekt mit der  PDO-Instanz.
    }

    /**
     * Getter-Methode zum Abrufen des FluentPDO Query-Objekts.
     *
     * @return Query
     */
    public function getFluent(): Query
    {
        return $this->fluent;
    }
}
