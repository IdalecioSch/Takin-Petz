<?php

namespace App\Controllers\Admin;

use App\Core\Domain;
use App\Models\Sintoma;
use App\Persistence\SintomaPersistence;

class SintomaController extends CrudController
{
    /** @return SintomaPersistence */
    protected function createPersistence() { return new SintomaPersistence(); }

    /** @return Sintoma */
    protected function createModel() { return new Sintoma(); }

    /** @return string */
    protected function getRoute() { return 'admin/sintomas'; }

    /** @return string */
    protected function getTitle() { return 'Sintomas'; }

    /** @return string */
    protected function getSingular() { return 'Sintoma'; }

    /** @return array */
    protected function getFields()
    {
        return [
            ['name' => 'nome', 'label' => 'Sintoma', 'type' => 'text', 'required' => true, 'max' => 80],
        ];
    }

    /** @return array */
    protected function getColumns()
    {
        return [['field' => 'nome', 'label' => 'Sintoma']];
    }
}
