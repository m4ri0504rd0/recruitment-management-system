<?php

namespace App\Controllers;

use App\Models\JobangebotModel;

class JobangeboteController extends BaseController
{
    // 1. Constructor & private Variablen
    private JobangebotModel $model;

    public function __construct()
    {
        $this->model = new JobangebotModel();
    }

    // 2. Public Methods (Entry points for routes)

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

//    Create-View für den GET-Request ist in der create-Methode implementiert>

    /**
     * Zeigt die show-View von jobangebote, mit dem selektierten Eintrag anhand seiner id an.
     *
     * @param $id int von dem Datensatz
     * @return void
     */
    public function showView(int $id): void
    {
        $job = $this->model->findById($id);

        parent::loadView('show', 'jobangebote', ['job' => $job]);
    }

    /**
     * Zeigt die edit-View von einem Datensatz an. Verwendet die create-View, um DRY einzuhalten.
     *
     * @param $id int von dem Datensatz
     * @return void
     */
    public function editView(int $id): void
    {
        $job = $this->model->findById($id);
        $this->loadView('create', 'jobangebote', ['data' => $job]);
    }

    /**
     * Zeigt die delete-View an, um ein Löschen zu bestätigen.
     *
     * @param $id int von dem Datensatz
     * @return void
     */
    public function deleteView(int $id)
    {
        $job = $this->model->findById($id);
        $this->loadView('delete', 'jobangebote', ['data' => $job]);
    }

    /**
     *
     * Bei GET-Request wird das Formular angezeigt, während bei einem POST-Request die Validierung inklusive
     * Form Data Persistence aka Sticky Forms durchgeführt wird.
     *
     *  Form Data Persistence:
     *  Eine Technik, bei der Benutzereingaben in einem Formular bei einem Validierungsfehler beibehalten
     *  und erneut angezeigt werden, damit der Benutzer nicht alle Daten erneut eingeben muss.
     *
     * @return void
     */
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

    /**
     * Aktualisiert einen Datensatz. Führt eine Validierung durch und setzt  Form Data Persistence aka Sticky Forms um.
     *
     * @return void
     */
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

    /**
     * Löscht ein Eintrag aus der Datenbank anhand seiner id.
     *
     * @return void
     */
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


    // 3. Private/Protected Methods (Helper- / Utility methods)

    /**
     * Sanitize (Bereinigung) von Eingabedaten durch Abschneiden und Ausblenden von HTML-Zeichen
     *
     * @param array $data
     * @return array
     */
    private function sanitizeInput(array $data): array
    {
        $sanitizedData = [];
        foreach ($data as $key => $value) {
            $sanitizedData[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
        return $sanitizedData;
    }

    /**
     * Iteriert über die angegebenen Felder und führt spezifische Validierungsprüfungen durch. Wenn ein Feld leer ist
     * oder nicht den Anforderungen entspricht, wird ein Fehler im errors-Array gespeichert.
     *
     * @param  array  $data
     * @return array
     */
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

}