<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Animal;
use App\Persistence\{AnimalPersistence, VacinaAplicadaPersistence};
use App\Utils\ControllerUtils;

class CatalogController extends Controller
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
        $filters = [];
        foreach (['especie', 'porte', 'sexo', 'faixa_etaria'] as $field) {
            $filters[$field] = ControllerUtils::getQuery($field);
        }
        $this->view('catalog/index', [
            'title' => 'Animais para adoção',
            'active' => 'animais',
            'filters' => $filters,
            'animals' => $this->Persistence->getCatalog($filters),
        ]);
    }

    /**
     * @param int $id
     * @return void
     */
    public function show($id)
    {
        $Animal = $this->Persistence->getModelById($id);
        if (!$Animal || !$Animal->isAvailable()) { // RN03
            $this->view('catalog/unavailable', ['title' => 'Animal não encontrado', 'active' => 'animais'], 'site', 404);
            return;
        }
        $this->view('catalog/show', [
            'title' => $Animal->nome,
            'active' => 'animais',
            'Animal' => $Animal,
            'vaccinations' => (new VacinaAplicadaPersistence())->getByAnimal($Animal->getId()),
        ]);
    }
}
