<?php

namespace App\Controllers\Admin;

use App\Core\Domain;
use App\Models\Cor;
use App\Persistence\CorPersistence;

class CorController extends CrudController
{
    /** @return CorPersistence */
    protected function createPersistence() { return new CorPersistence(); }

    /** @return Cor */
    protected function createModel() { return new Cor(); }

    /** @return string */
    protected function getRoute() { return 'admin/cores'; }

    /** @return string */
    protected function getTitle() { return 'Cores'; }

    /** @return string */
    protected function getSingular() { return 'Cor'; }

    /** @return array */
    protected function getFields()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome da cor', 'type' => 'text', 'required' => true, 'max' => 60],
        ];
    }

    /** @return array */
    protected function getColumns()
    {
        return [['field' => 'nome', 'label' => 'Cor']];
    }
}
