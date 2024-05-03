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