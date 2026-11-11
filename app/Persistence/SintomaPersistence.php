<?php

namespace App\Persistence;

use App\Core\{Model, Persistence};
use App\Models\Sintoma;

class SintomaPersistence extends Persistence
{
    /** @return string */
    public function getTable() { return 'sintomas'; }

    /** @return string */
    public function getModelClass() { return Sintoma::class; }

    /** @return string */
    public function getDefaultOrderBy() { return 't.nome'; }

    /** @return string */
    public function getSqlInsert() { return 'INSERT INTO sintomas (nome) VALUES (:nome)'; }

    /** @return string */
    public function getSqlUpdate() { return 'UPDATE sintomas SET nome = :nome WHERE id = :id'; }

    /**
     * @param Model $Model
     * @return array
     */
    public function getParams(Model $Model) { return ['nome' => $Model->nome]; }
}
