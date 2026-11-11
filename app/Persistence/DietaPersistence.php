<?php

namespace App\Persistence;

use App\Core\{Database, Model, Persistence};
use App\Models\Dieta;

class DietaPersistence extends Persistence
{
    /** @return string */
    public function getTable() { return 'dietas'; }

    /** @return string */
    public function getModelClass() { return Dieta::class; }

    /** @return string */
    public function getDefaultOrderBy() { return '(t.encerrada_em IS NULL) DESC, t.iniciada_em DESC, t.id DESC'; }

    /** @return string */
    public function getSqlInsert()
    {
        return 'INSERT INTO dietas (animal_id, tipo_alimentacao, descricao, refeicoes_por_dia, restricoes, iniciada_em, encerrada_em)
                VALUES (:animal_id, :tipo_alimentacao, :descricao, :refeicoes_por_dia, :restricoes, :iniciada_em, :encerrada_em)';
    }

    /** @return string */
    public function getSqlUpdate()
    {
        return 'UPDATE dietas
                   SET tipo_alimentacao = :tipo_alimentacao, descricao = :descricao, refeicoes_por_dia = :refeicoes_por_dia,
                       restricoes = :restricoes, iniciada_em = :iniciada_em, encerrada_em = :encerrada_em
                 WHERE id = :id';
    }

    /**
     * @param Model $Model
     * @return array
     */
    public function getParams(Model $Model)
    {
        return [
            'animal_id' => $Model->animal_id,
            'tipo_alimentacao' => $Model->tipo_alimentacao,
            'descricao' => $Model->descricao ?: null,
            'refeicoes_por_dia' => $Model->refeicoes_por_dia ?: null,
            'restricoes' => $Model->restricoes ?: null,
            'iniciada_em' => $Model->iniciada_em,
            'encerrada_em' => $Model->encerrada_em ?: null,
        ];
    }

    /**
     * @param int $animalId
     * @return Dieta[]
     */
    public function getByAnimal($animalId)
    {
        return $this->getAllModel(['t.animal_id = :animal_id'], ['animal_id' => $animalId]);
    }

    /**
     * @param Dieta $Dieta
     * @param string $date
     * @return Dieta
     */
    public function end(Dieta $Dieta, $date)
    {
        Database::execute('UPDATE dietas SET encerrada_em = GREATEST(:date::date, iniciada_em) WHERE id = :id', ['date' => $date, 'id' => $Dieta->getId()]);
        return $this->refresh($Dieta);
    }
}
