<?php

namespace App\Controllers;

class HomeController
{
    // Gibt das echo-statement zurück wenn ein request an localhost/ gesendet wird.
    public function index(): void
    {
        echo 'Hallo von src/Views/Home/index.php';
    }
}