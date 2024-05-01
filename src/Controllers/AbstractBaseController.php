<?php

namespace App\Controllers;

abstract class AbstractBaseController
{
    // Erzwingt die Definition dieser Methode durch die erweiternde Klasse. Funktioniert nicht bei Eigenschaften.
    abstract public function loadView($viewName, $subDir = '', $data = []): void;
}