<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Animal;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home/index', ['titulo' => 'Início', 'ativo' => '', 'recentes' => Animal::recentes(4)]);
    }
}
