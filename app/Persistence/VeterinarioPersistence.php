<?php

namespace App\Persistence;

use App\Core\{Model, Persistence};
use App\Models\Veterinario;

class VeterinarioPersistence extends Persistence
{
    /** @return string */
    public function getTable() { return 'veterinarios'; }

    /** @return string */
    public function getModelClass() { return Veterinario::class; }

    /** @return string */
    public function getDefaultOrderBy() { return 't.nome'; }

    /** @return string */
    public function getSqlInsert()
    {
        return 'INSERT INTO veterinarios (nome, clinica, telefone, crmv) VALUES (:nome, :clinica, :telefone, :crmv)';
    }

    /** @return string */
    public function getSqlUpdate()
    {
        return 'UPDATE veterinarios SET nome = :nome, clinica = :clinica, telefone = :telefone, crmv = :crmv WHERE id = :id';
    }

    /**
     * @param Model $Model
     * @return array
     */
    public function getParams(Model $Model)
    {
        return ['nome' => $Model->nome, 'clinica' => $Model->clinica, 'telefone' => $Model->telefone, 'crmv' => $Model->crmv];
    }
}
