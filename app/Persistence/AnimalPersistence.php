<?php

namespace App\Persistence;

use App\Core\{BusinessException, Database, Domain, Messages, Model, Persistence};
use App\Models\{Animal, Foto};
use App\Utils\{ModelUtils, PersistenciaUtils};

class AnimalPersistence extends Persistence
{
    /** @return string */
    public function getTable() { return 'animais'; }

    /** @return string */
    public function getModelClass() { return Animal::class; }

    /** @return string */
    public function getDefaultOrderBy() { return 't.criado_em DESC, t.id DESC'; }

    /** @return string */
    public function getSqlGetAll()
    {
        return "SELECT t.*,
                       r.nome AS raca_nome,
                       c.nome AS cor_nome,
                       (SELECT f.arquivo FROM fotos_animais f WHERE f.animal_id = t.id
                         ORDER BY f.principal DESC, f.ordem, f.id LIMIT 1) AS foto,
                       (SELECT count(*) FROM solicitacoes_adocao s WHERE s.animal_id = t.id) AS total_pedidos,
                       (SELECT count(*) FROM solicitacoes_adocao s WHERE s.animal_id = t.id AND s.status = 'pendente') AS pedidos_pendentes
                  FROM animais t
                  LEFT JOIN racas r ON r.id = t.raca_id
                  LEFT JOIN cores c ON c.id = t.cor_id";
    }

    /** @return string */
    public function getSqlInsert()
    {
        return 'INSERT INTO animais (nome, especie, sexo, porte, faixa_etaria, raca_id, cor_id, descricao, temperamento,
                                     castrado, vacinado, vermifugado, status)
                VALUES (:nome, :especie, :sexo, :porte, :faixa_etaria, :raca_id, :cor_id, :descricao, :temperamento,
                        :castrado, :vacinado, :vermifugado, :status)';
    }

    /** @return string */
    public function getSqlUpdate()
    {
        return 'UPDATE animais
                   SET nome = :nome, especie = :especie, sexo = :sexo, porte = :porte, faixa_etaria = :faixa_etaria,
                       raca_id = :raca_id, cor_id = :cor_id, descricao = :descricao, temperamento = :temperamento,
                       castrado = :castrado, vacinado = :vacinado, vermifugado = :vermifugado, status = :status,
                       atualizado_em = now()
                 WHERE id = :id';
    }

    /**
     * @param Model $Model
     * @return array
     */
    public function getParams(Model $Model)
    {
        return [
            'nome' => $Model->nome,
            'especie' => $Model->especie,
            'sexo' => $Model->sexo,
            'porte' => $Model->porte,
            'faixa_etaria' => $Model->faixa_etaria,
            'raca_id' => $Model->raca_id ?: null,
            'cor_id' => $Model->cor_id ?: null,
            'descricao' => $Model->descricao,
            'temperamento' => $Model->temperamento,
            'castrado' => (bool)$Model->castrado,
            'vacinado' => (bool)$Model->vacinado,
            'vermifugado' => (bool)$Model->vermifugado,
            'status' => $Model->status ?: 'disponivel',
        ];
    }

    /**
     * @param Model $Model
     * @return void
     */
    protected function afterRefresh(Model $Model)
    {
        $Model->photos = (new FotoPersistence())->getByAnimal($Model->getId());
    }

    /**
     * @param array $filters
     * @return Animal[]
     */
    public function getCatalog($filters)
    {
        $conditions = ["t.status = 'disponivel'"];
        $params = [];
        $columns = ['especie' => 'especies', 'porte' => 'portes', 'sexo' => 'sexos', 'faixa_etaria' => 'faixas_etarias'];
        foreach ($columns as $column => $table) {
            $value = isset($filters[$column]) ? $filters[$column] : '';
            if (Domain::isValid($table, $value)) {
                $conditions[] = 't.' . $column . ' = :' . $column;
                $params[$column] = $value;
            }
        }
        return $this->getAllModel($conditions, $params, null, 100);
    }

    /**
     * @param int $limit
     * @return Animal[]
     */
    public function getRecent($limit = 4)
    {
        return $this->getAllModel(["t.status = 'disponivel'"], [], null, $limit);
    }

    /**
     * @param string $search
     * @param string $status
     * @return Animal[]
     */
    public function getForAdmin($search, $status)
    {
        $conditions = [];
        $params = [];
        if ($search !== '') {
            $conditions[] = 't.nome ILIKE :search';
            $params['search'] = PersistenciaUtils::like($search);
        }
        if (Domain::isValid('status_animal', $status)) {
            $conditions[] = 't.status = :status';
            $params['status'] = $status;
        }
        return $this->getAllModel($conditions, $params);
    }

    /**
     * @return array
     */
    public function countByStatus()
    {
        $counts = array_fill_keys(array_keys(Domain::getOptions('status_animal')), 0);
        foreach (Database::fetchAll('SELECT status, count(*) AS total FROM animais GROUP BY status') as $row) {
            $counts[$row['status']] = (int)$row['total'];
        }
        return $counts;
    }

    /**
     * @param int $id
     * @return void
     */
    public function lockById($id)
    {
        Database::fetchValue('SELECT id FROM animais WHERE id = :id FOR UPDATE', ['id' => $id]);
    }

    /**
     * @param Animal $Animal
     * @param string $status
     * @return Animal
     */
    public function updateStatus(Animal $Animal, $status)
    {
        Database::execute('UPDATE animais SET status = :status, atualizado_em = now() WHERE id = :id', ['status' => $status, 'id' => $Animal->getId()]);
        return $this->refresh($Animal);
    }

    /**
     * @param Animal $Animal
     * @return string[]
     * @throws BusinessException
     */
    public function deleteSafely(Animal $Animal)
    {
        if ($this->hasRequests($Animal)) {
            throw new BusinessException(Messages::get(Messages::ANIMAL_HAS_REQUESTS, $Animal->nome));
        }
        $files = ModelUtils::pluck((new FotoPersistence())->getByAnimal($Animal->getId()), 'arquivo');
        $this->delete($Animal);
        return $files;
    }

    /**
     * @param Animal $Animal
     * @return bool
     */
    public function hasRequests(Animal $Animal)
    {
        return (int)Database::fetchValue('SELECT count(*) FROM solicitacoes_adocao WHERE animal_id = :id', ['id' => $Animal->getId()]) > 0;
    }

    /**
     * @param Animal $Animal
     * @param int[] $removeIds
     * @param string[] $newFiles
     * @param int $mainId
     * @return string[]
     */
    public function syncPhotos(Animal $Animal, $removeIds, $newFiles, $mainId = 0)
    {
        $FotoPersistence = new FotoPersistence();
        $removedFiles = [];
        foreach ($FotoPersistence->getByAnimal($Animal->getId()) as $Foto) {
            if (in_array($Foto->getId(), $removeIds, true)) {
                $removedFiles[] = $Foto->arquivo;
                $FotoPersistence->delete($Foto);
            }
        }

        $order = (int)Database::fetchValue('SELECT COALESCE(MAX(ordem), 0) FROM fotos_animais WHERE animal_id = :id', ['id' => $Animal->getId()]);
        foreach ($newFiles as $file) {
            $FotoPersistence->insert(new Foto(['animal_id' => $Animal->getId(), 'arquivo' => $file, 'ordem' => ++$order, 'principal' => false]));
        }

        $ids = ModelUtils::getIds($FotoPersistence->getAllModel(['t.animal_id = :id'], ['id' => $Animal->getId()], 't.ordem, t.id'));
        if ($ids) {
            $mainId = in_array($mainId, $ids, true) ? $mainId : $ids[0];
            Database::execute('UPDATE fotos_animais SET principal = false WHERE animal_id = :id', ['id' => $Animal->getId()]);
            Database::execute('UPDATE fotos_animais SET principal = true WHERE id = :photo', ['photo' => $mainId]);
        }
        return $removedFiles;
    }
}
