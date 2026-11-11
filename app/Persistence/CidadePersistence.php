<?php

namespace App\Persistence;

use App\Core\{Database, Model, Persistence};
use App\Models\Cidade;

class CidadePersistence extends Persistence
{
    /** @return string */
    public function getTable() { return 'cidades'; }

    /** @return string */
    public function getModelClass() { return Cidade::class; }

    /** @return string */
    public function getDefaultOrderBy() { return 't.nome'; }

    /** @return string */
    public function getSqlInsert() { return 'INSERT INTO cidades (nome, uf) VALUES (:nome, :uf)'; }

    /** @return string */
    public function getSqlUpdate() { return 'UPDATE cidades SET nome = :nome, uf = :uf WHERE id = :id'; }

    /**
     * @param Model $Model
     * @return array
     */
    public function getParams(Model $Model) { return ['nome' => $Model->nome, 'uf' => $Model->uf]; }

    /**
     * @param string $uf
     * @return Cidade[]
     */
    public function getByState($uf)
    {
        return $this->getAllModel(['t.uf = :uf'], ['uf' => $uf], 't.nome');
    }

    /**
     * @param string $uf
     * @return int
     */
    public function countByState($uf)
    {
        return $this->count(['t.uf = :uf'], ['uf' => $uf]);
    }

    /**
     * @param string $uf
     * @param string[] $names
     * @return void
     */
    public function insertMany($uf, $names)
    {
        Database::transaction(function () use ($uf, $names) {
            foreach ($names as $name) {
                Database::execute('INSERT INTO cidades (nome, uf) VALUES (:nome, :uf) ON CONFLICT DO NOTHING', ['nome' => $name, 'uf' => $uf]);
            }
        });
    }

    /**
     * @param string $name
     * @param string $uf
     * @return Cidade
     */
    public function obtain($name, $uf)
    {
        $Cidade = $this->getFirstModel(['t.uf = :uf', 'lower(t.nome) = lower(:nome)'], ['uf' => $uf, 'nome' => $name]);
        return $Cidade ?: $this->save(new Cidade(['nome' => $name, 'uf' => $uf]));
    }
}
