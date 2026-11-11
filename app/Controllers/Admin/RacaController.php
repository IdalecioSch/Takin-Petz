<?php

namespace App\Controllers\Admin;

use App\Core\Domain;
use App\Models\Raca;
use App\Persistence\RacaPersistence;

class RacaController extends CrudController
{
    /** @return RacaPersistence */
    protected function createPersistence() { return new RacaPersistence(); }

    /** @return Raca */
    protected function createModel() { return new Raca(); }

    /** @return string */
    protected function getRoute() { return 'admin/racas'; }

    /** @return string */
    protected function getTitle() { return 'Raças'; }

    /** @return string */
    protected function getSingular() { return 'Raça'; }

    /** @return array */
    protected function getFields()
    {
        return [
            ['name' => 'especie', 'label' => 'Espécie', 'type' => 'select', 'required' => true, 'options' => Domain::getOptions('especies')],
            ['name' => 'nome', 'label' => 'Nome da raça', 'type' => 'text', 'required' => true, 'max' => 80],
        ];
    }

    /** @return array */
    protected function getColumns()
    {
        return [['field' => 'nome', 'label' => 'Raça'], ['field' => 'especie_nome', 'label' => 'Espécie']];
    }
}
