<?php

namespace App\Persistence;

use App\Core\{Model, Persistence};
use App\Models\Foto;

class FotoPersistence extends Persistence
{
    /** @return string */
    public function getTable() { return 'fotos_animais'; }

    /** @return string */
    public function getModelClass() { return Foto::class; }

    /** @return string */
    public function getDefaultOrderBy() { return 't.principal DESC, t.ordem, t.id'; }

    /** @return string */
    public function getSqlInsert()
    {
        return 'INSERT INTO fotos_animais (animal_id, arquivo, principal, ordem) VALUES (:animal_id, :arquivo, :principal, :ordem)';
    }

    /** @return string */
    public function getSqlUpdate()
    {
        return 'UPDATE fotos_animais SET arquivo = :arquivo, principal = :principal, ordem = :ordem WHERE id = :id';
    }

    /**
     * @param Model $Model
     * @return array
     */
    public function getParams(Model $Model)
    {
        return [
            'animal_id' => $Model->animal_id,
            'arquivo' => $Model->arquivo,
            'principal' => (bool)$Model->principal,
            'ordem' => $Model->ordem,
        ];
    }

    /**
     * @param int $animalId
     * @return Foto[]
     */
    public function getByAnimal($animalId)
    {
        return $this->getAllModel(['t.animal_id = :animal_id'], ['animal_id' => $animalId]);
    }
}
