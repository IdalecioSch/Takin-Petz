<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Animal;
use App\Persistence\AnimalPersistence;

class HomeController extends Controller
{
    /** @return AnimalPersistence */
    protected function createPersistence() { return new AnimalPersistence(); }

    /** @return Animal */
    protected function createModel() { return new Animal(); }

    /**
     * @return void
     */
    public function index()
    {
        $counts = $this->Persistence->countByStatus();
        $this->view('home/index', [
            'title' => 'Início',
            'active' => '',
            'recent' => $this->Persistence->getRecent(4),
            'availableCount' => $counts['disponivel'],
            'adoptedCount' => $counts['adotado'],
        ]);
    }
}
