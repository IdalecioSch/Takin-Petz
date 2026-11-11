<?php

namespace App\Controllers\Admin;

use App\Core\Domain;
use App\Models\Veterinario;
use App\Persistence\VeterinarioPersistence;

class VeterinarioController extends CrudController
{
    /** @return VeterinarioPersistence */
    protected function createPersistence() { return new VeterinarioPersistence(); }

    /** @return Veterinario */
    protected function createModel() { return new Veterinario(); }

    /** @return string */
    protected function getRoute() { return 'admin/veterinarios'; }

    /** @return string */
    protected function getTitle() { return 'Veterinários'; }

    /** @return string */
    protected function getSingular() { return 'Veterinário'; }

    /** @return array */
    protected function getFields()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'required' => true, 'max' => 120],
            ['name' => 'clinica', 'label' => 'Clínica', 'type' => 'text', 'max' => 120],
            ['name' => 'telefone', 'label' => 'Telefone', 'type' => 'text', 'max' => 30],
            ['name' => 'crmv', 'label' => 'CRMV', 'type' => 'text', 'max' => 30],
        ];
    }

    /** @return array */
    protected function getColumns()
    {
        return [['field' => 'nome', 'label' => 'Nome'], ['field' => 'clinica', 'label' => 'Clínica'], ['field' => 'telefone', 'label' => 'Telefone']];
    }
}
