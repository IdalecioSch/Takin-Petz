<?php

namespace App\Controllers;

use App\Core\Controller;

class PageController extends Controller
{
    /**
     * @return void
     */
    public function about()
    {
        $this->view('page/about', ['title' => 'Sobre', 'active' => 'sobre']);
    }
}
