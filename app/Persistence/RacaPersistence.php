<?php

namespace App\Persistence;

use App\Core\{Model, Persistence};
use App\Models\Raca;

class RacaPersistence extends Persistence
{
    /** @return string */
    public function getTable() { return 'racas'; }

    /** @return string */
    public function getModelClass() { return Raca::class; }

    /** @return string */
    public function getDefaultOrderBy() { return 't.especie, t.nome'; }

    /** @return string */
    public function getSqlGetAll()
    {
        return 'SELECT t.*, e.nome AS especie_nome FROM racas t JOIN especies e ON e.codigo = t.especie';
    }

    /** @return string */
    public function getSqlInsert() { return 'INSERT INTO racas (especie, nome) VALUES (:especie, :nome)'; }

    /** @return string */
    public function getSqlUpdate() { return 'UPDATE racas SET especie = :especie, nome = :nome WHERE id = :id'; }

    /**
     * @param Model $Model
     * @return array
     */
    public function getParams(Model $Model) { return ['especie' => $Model->especie, 'nome' => $Model->nome]; }
}
