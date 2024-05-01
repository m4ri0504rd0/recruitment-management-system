<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class JobangeboteController extends BaseController
{
    // Zeigt die index-View von Jobangebote an
    public function index()
    {
//        $this->loadView('index','jobangebote');     // Ok
        parent::loadView('index', 'jobangebote');   // Besser
    }
}