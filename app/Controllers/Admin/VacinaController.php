<?php

namespace App\Controllers\Admin;

use App\Core\Domain;
use App\Models\Vacina;
use App\Persistence\VacinaPersistence;

class VacinaController extends CrudController
{
    /** @return VacinaPersistence */
    protected function createPersistence() { return new VacinaPersistence(); }

    /** @return Vacina */
    protected function createModel() { return new Vacina(); }

    /** @return string */
    protected function getRoute() { return 'admin/vacinas'; }

    /** @return string */
    protected function getTitle() { return 'Vacinas'; }

    /** @return string */
    protected function getSingular() { return 'Vacina'; }

    /** @return array */
    protected function getFields()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome da vacina', 'type' => 'text', 'required' => true, 'max' => 80],
            ['name' => 'especie', 'label' => 'Espécie', 'type' => 'select', 'options' => Domain::getOptions('especies'), 'empty' => 'Qualquer espécie'],
        ];
    }

    /** @return array */
    protected function getColumns()
    {
        return [['field' => 'nome', 'label' => 'Vacina'], ['field' => 'especie_nome', 'label' => 'Espécie']];
    }
}
