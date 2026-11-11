<?php

namespace App\Persistence;

use App\Core\{BusinessException, Database, Domain, Messages, Model, Persistence};
use App\Models\{Administrador, Animal, Pessoa, Solicitacao};
use App\Utils\DateUtils;

class SolicitacaoPersistence extends Persistence
{
    /** @return string */
    public function getTable() { return 'solicitacoes_adocao'; }

    /** @return string */
    public function getModelClass() { return Solicitacao::class; }

    /** @return string */
    public function getDefaultOrderBy() { return "(t.status = 'pendente') DESC, t.criado_em DESC"; }

    /** @return string */
    public function getSqlGetAll()
    {
        return 'SELECT t.*,
                       a.nome AS animal_nome, a.status AS animal_status,
                       p.nome AS pessoa_nome, p.email AS pessoa_email, p.telefone AS pessoa_telefone,
                       c.nome AS cidade_nome, c.uf AS cidade_uf,
                       d.nome AS decisor_nome
                  FROM solicitacoes_adocao t
                  JOIN animais a ON a.id = t.animal_id
                  JOIN pessoas p ON p.id = t.pessoa_id
                  LEFT JOIN cidades c ON c.id = p.cidade_id
                  LEFT JOIN usuarios_admin d ON d.id = t.decidido_por';
    }

    /** @return string */
    public function getSqlInsert()
    {
        return 'INSERT INTO solicitacoes_adocao (animal_id, pessoa_id, tipo_moradia, tem_outros_animais, motivacao, termo_aceito_em, lgpd_aceito_em)
                VALUES (:animal_id, :pessoa_id, :tipo_moradia, :tem_outros_animais, :motivacao, :termo_aceito_em, :lgpd_aceito_em)';
    }

    /** @return string */
    public function getSqlUpdate()
    {
        return 'UPDATE solicitacoes_adocao
                   SET tipo_moradia = :tipo_moradia, tem_outros_animais = :tem_outros_animais, motivacao = :motivacao,
                       status = :status, motivo_recusa = :motivo_recusa, decidido_em = :decidido_em, decidido_por = :decidido_por
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
            'pessoa_id' => $Model->pessoa_id,
            'tipo_moradia' => $Model->tipo_moradia,
            'tem_outros_animais' => (bool)$Model->tem_outros_animais,
            'motivacao' => $Model->motivacao,
            'termo_aceito_em' => $Model->termo_aceito_em,
            'lgpd_aceito_em' => $Model->lgpd_aceito_em,
            'status' => $Model->status ?: 'pendente',
            'motivo_recusa' => $Model->motivo_recusa,
            'decidido_em' => $Model->decidido_em,
            'decidido_por' => $Model->decidido_por,
        ];
    }

    /**
     * @param string $status
     * @param int $animalId
     * @return Solicitacao[]
     */
    public function getList($status, $animalId)
    {
        $conditions = [];
        $params = [];
        if (Domain::isValid('status_solicitacao', $status)) {
            $conditions[] = 't.status = :status';
            $params['status'] = $status;
        }
        if ($animalId > 0) {
            $conditions[] = 't.animal_id = :animal_id';
            $params['animal_id'] = $animalId;
        }
        return $this->getAllModel($conditions, $params, null, 200);
    }

    /**
     * @param int $limit
     * @return Solicitacao[]
     */
    public function getRecentPending($limit = 5)
    {
        return $this->getAllModel(["t.status = 'pendente'"], [], 't.criado_em DESC', $limit);
    }

    /**
     * @return int
     */
    public function countPending()
    {
        return $this->count(["t.status = 'pendente'"]);
    }

    /**
     * @param Animal $Animal
     * @param Pessoa $Pessoa
     * @return bool
     */
    public function hasPending(Animal $Animal, Pessoa $Pessoa)
    {
        return $this->count(["t.animal_id = :animal_id", "t.pessoa_id = :pessoa_id", "t.status = 'pendente'"],
            ['animal_id' => $Animal->getId(), 'pessoa_id' => $Pessoa->getId()]) > 0;
    }

    /**
     * @param Animal $Animal
     * @param Pessoa $Pessoa
     * @param array $data
     * @return Solicitacao
     */
    public function open(Animal $Animal, Pessoa $Pessoa, $data)
    {
        $now = DateUtils::now();
        return $this->save(new Solicitacao([
            'animal_id' => $Animal->getId(),
            'pessoa_id' => $Pessoa->getId(),
            'tipo_moradia' => $data['housing'],
            'tem_outros_animais' => (bool)$data['has_other_pets'],
            'motivacao' => $data['motivation'],
            'termo_aceito_em' => $now,
            'lgpd_aceito_em' => $now,
        ]));
    }

    /**
     * @param Solicitacao $Solicitacao
     * @param Administrador $Admin
     * @return Solicitacao[]
     * @throws BusinessException
     */
    public function approve(Solicitacao $Solicitacao, Administrador $Admin)
    {
        return Database::transaction(function () use ($Solicitacao, $Admin) {
            $AnimalPersistence = new AnimalPersistence();
            $Animal = $this->lockAndGetAnimal($Solicitacao, $AnimalPersistence);
            if ($Animal->status === 'adotado') {
                throw new BusinessException(Messages::ANIMAL_ALREADY_ADOPTED);
            }
            $this->decide($Solicitacao, 'aprovado', $Admin, null);
            $AnimalPersistence->updateStatus($Animal, 'adotado');

            $others = $this->getAllModel(["t.animal_id = :animal_id", "t.status = 'pendente'", "t.id <> :id"],
                ['animal_id' => $Animal->getId(), 'id' => $Solicitacao->getId()]);
            foreach ($others as $Other) {
                $this->decide($Other, 'recusado', $Admin, Messages::AUTOMATIC_REFUSAL);
            }
            return $others;
        });
    }

    /**
     * @param Solicitacao $Solicitacao
     * @param Administrador $Admin
     * @param string|null $reason
     * @return void
     * @throws BusinessException
     */
    public function refuse(Solicitacao $Solicitacao, Administrador $Admin, $reason)
    {
        Database::transaction(function () use ($Solicitacao, $Admin, $reason) {
            $this->lockAndGetAnimal($Solicitacao, new AnimalPersistence());
            $this->decide($Solicitacao, 'recusado', $Admin, $reason ?: null);
        });
    }

    /**
     * @param Solicitacao $Solicitacao
     * @param AnimalPersistence $AnimalPersistence
     * @return Animal
     * @throws BusinessException
     */
    private function lockAndGetAnimal(Solicitacao $Solicitacao, AnimalPersistence $AnimalPersistence)
    {
        // ordem fixa (animal, depois pedido) evita deadlock entre decisões simultâneas
        $AnimalPersistence->lockById($Solicitacao->animal_id);
        $status = Database::fetchValue('SELECT status FROM solicitacoes_adocao WHERE id = :id FOR UPDATE', ['id' => $Solicitacao->getId()]);
        if ($status !== 'pendente') {
            throw new BusinessException(Messages::REQUEST_ALREADY_DECIDED);
        }
        return $AnimalPersistence->getModelByIdOrFail($Solicitacao->animal_id);
    }

    /**
     * @param Solicitacao $Solicitacao
     * @param string $status
     * @param Administrador $Admin
     * @param string|null $reason
     * @return void
     */
    private function decide(Solicitacao $Solicitacao, $status, Administrador $Admin, $reason)
    {
        Database::execute(
            'UPDATE solicitacoes_adocao
                SET status = :status, motivo_recusa = :reason, decidido_em = now(), decidido_por = :admin
              WHERE id = :id',
            ['status' => $status, 'reason' => $status === 'recusado' ? $reason : null, 'admin' => $Admin->getId(), 'id' => $Solicitacao->getId()]
        );
        $this->refresh($Solicitacao);
    }
}
