<?php

namespace App\Persistence;

use App\Core\{Model, Persistence};
use App\Models\VacinaAplicada;

class VacinaAplicadaPersistence extends Persistence
{
    /** @return string */
    public function getTable() { return 'animais_vacinas'; }

    /** @return string */
    public function getModelClass() { return VacinaAplicada::class; }

    /** @return string */
    public function getDefaultOrderBy() { return 't.aplicada_em DESC, t.id DESC'; }

    /** @return string */
    public function getSqlGetAll()
    {
        return 'SELECT t.*, v.nome AS vacina_nome, vet.nome AS veterinario_nome
                  FROM animais_vacinas t
                  JOIN vacinas v ON v.id = t.vacina_id
                  LEFT JOIN veterinarios vet ON vet.id = t.veterinario_id';
    }

    /** @return string */
    public function getSqlInsert()
    {
        return 'INSERT INTO animais_vacinas (animal_id, vacina_id, aplicada_em, proxima_dose_em, veterinario_id, observacoes)
                VALUES (:animal_id, :vacina_id, :aplicada_em, :proxima_dose_em, :veterinario_id, :observacoes)';
    }

    /** @return string */
    public function getSqlUpdate()
    {
        return 'UPDATE animais_vacinas
                   SET vacina_id = :vacina_id, aplicada_em = :aplicada_em, proxima_dose_em = :proxima_dose_em,
                       veterinario_id = :veterinario_id, observacoes = :observacoes
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
            'vacina_id' => $Model->vacina_id,
            'aplicada_em' => $Model->aplicada_em,
            'proxima_dose_em' => $Model->proxima_dose_em ?: null,
            'veterinario_id' => $Model->veterinario_id ?: null,
            'observacoes' => $Model->observacoes ?: null,
        ];
    }

    /**
     * @param int $animalId
     * @return VacinaAplicada[]
     */
    public function getByAnimal($animalId)
    {
        return $this->getAllModel(['t.animal_id = :animal_id'], ['animal_id' => $animalId]);
    }
}
