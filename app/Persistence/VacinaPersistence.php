<?php

namespace App\Persistence;

use App\Core\{Model, Persistence};
use App\Models\Vacina;

class VacinaPersistence extends Persistence
{
    /** @return string */
    public function getTable() { return 'vacinas'; }

    /** @return string */
    public function getModelClass() { return Vacina::class; }

    /** @return string */
    public function getDefaultOrderBy() { return 't.nome'; }

    /** @return string */
    public function getSqlGetAll()
    {
        return 'SELECT t.*, e.nome AS especie_nome FROM vacinas t LEFT JOIN especies e ON e.codigo = t.especie';
    }

    /** @return string */
    public function getSqlInsert() { return 'INSERT INTO vacinas (nome, especie) VALUES (:nome, :especie)'; }

    /** @return string */
    public function getSqlUpdate() { return 'UPDATE vacinas SET nome = :nome, especie = :especie WHERE id = :id'; }

    /**
     * @param Model $Model
     * @return array
     */
    public function getParams(Model $Model) { return ['nome' => $Model->nome, 'especie' => $Model->especie ?: null]; }

    /**
     * @param string $species
     * @return Vacina[]
     */
    public function getBySpecies($species)
    {
        return $this->getAllModel(['(t.especie IS NULL OR t.especie = :especie)'], ['especie' => $species], 't.nome');
    }
}
