<?php

namespace App\Persistence;

use App\Core\{Model, Persistence};
use App\Models\Cor;

class CorPersistence extends Persistence
{
    /** @return string */
    public function getTable() { return 'cores'; }

    /** @return string */
    public function getModelClass() { return Cor::class; }

    /** @return string */
    public function getDefaultOrderBy() { return 't.nome'; }

    /** @return string */
    public function getSqlInsert() { return 'INSERT INTO cores (nome) VALUES (:nome)'; }

    /** @return string */
    public function getSqlUpdate() { return 'UPDATE cores SET nome = :nome WHERE id = :id'; }

    /**
     * @param Model $Model
     * @return array
     */
    public function getParams(Model $Model) { return ['nome' => $Model->nome]; }
}
