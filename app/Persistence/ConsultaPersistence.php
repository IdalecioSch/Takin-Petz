<?php

namespace App\Persistence;

use App\Core\{Database, Model, Persistence};
use App\Models\Consulta;

class ConsultaPersistence extends Persistence
{
    /** @return string */
    public function getTable() { return 'consultas'; }

    /** @return string */
    public function getModelClass() { return Consulta::class; }

    /** @return string */
    public function getDefaultOrderBy() { return 't.consultada_em DESC, t.id DESC'; }

    /** @return string */
    public function getSqlGetAll()
    {
        return "SELECT t.*, v.nome AS veterinario_nome,
                       (SELECT string_agg(s.nome, ', ' ORDER BY s.nome)
                          FROM consultas_sintomas cs JOIN sintomas s ON s.id = cs.sintoma_id
                         WHERE cs.consulta_id = t.id) AS sintomas_lista
                  FROM consultas t
                  LEFT JOIN veterinarios v ON v.id = t.veterinario_id";
    }

    /** @return string */
    public function getSqlInsert()
    {
        return 'INSERT INTO consultas (animal_id, veterinario_id, consultada_em, motivo, diagnostico, tratamento, observacoes, retorno_em)
                VALUES (:animal_id, :veterinario_id, :consultada_em, :motivo, :diagnostico, :tratamento, :observacoes, :retorno_em)';
    }

    /** @return string */
    public function getSqlUpdate()
    {
        return 'UPDATE consultas
                   SET veterinario_id = :veterinario_id, consultada_em = :consultada_em, motivo = :motivo,
                       diagnostico = :diagnostico, tratamento = :tratamento, observacoes = :observacoes, retorno_em = :retorno_em
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
            'veterinario_id' => $Model->veterinario_id ?: null,
            'consultada_em' => $Model->consultada_em,
            'motivo' => $Model->motivo,
            'diagnostico' => $Model->diagnostico ?: null,
            'tratamento' => $Model->tratamento ?: null,
            'observacoes' => $Model->observacoes ?: null,
            'retorno_em' => $Model->retorno_em ?: null,
        ];
    }

    /**
     * @param int $animalId
     * @return Consulta[]
     */
    public function getByAnimal($animalId)
    {
        return $this->getAllModel(['t.animal_id = :animal_id'], ['animal_id' => $animalId]);
    }

    /**
     * @param Consulta $Consulta
     * @param int[] $symptomIds
     * @return Consulta
     */
    public function saveWithSymptoms(Consulta $Consulta, $symptomIds)
    {
        return Database::transaction(function () use ($Consulta, $symptomIds) {
            $this->save($Consulta);
            Database::execute('DELETE FROM consultas_sintomas WHERE consulta_id = :id', ['id' => $Consulta->getId()]);
            foreach (array_unique($symptomIds) as $symptomId) {
                Database::execute('INSERT INTO consultas_sintomas (consulta_id, sintoma_id) VALUES (:consulta, :sintoma)',
                    ['consulta' => $Consulta->getId(), 'sintoma' => $symptomId]);
            }
            return $this->refresh($Consulta);
        });
    }
}
