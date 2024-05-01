<?php
// Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Instanziierung HomeController & call der index-Methode
$controller = new App\Controllers\HomeController();
$controller->index();