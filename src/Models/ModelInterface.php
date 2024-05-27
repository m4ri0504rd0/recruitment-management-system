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