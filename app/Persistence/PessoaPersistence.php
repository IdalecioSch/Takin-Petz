<?php

namespace App\Persistence;

use App\Core\{Database, Model, Persistence};
use App\Models\{Cidade, Pessoa};
use App\Utils\StringUtils;

class PessoaPersistence extends Persistence
{
    /** @return string */
    public function getTable() { return 'pessoas'; }

    /** @return string */
    public function getModelClass() { return Pessoa::class; }

    /** @return string */
    public function getSqlGetAll()
    {
        return 'SELECT t.*, c.nome AS cidade_nome, c.uf AS cidade_uf FROM pessoas t LEFT JOIN cidades c ON c.id = t.cidade_id';
    }

    /** @return string */
    public function getSqlInsert()
    {
        return 'INSERT INTO pessoas (nome, email, telefone, cidade_id) VALUES (:nome, :email, :telefone, :cidade_id)';
    }

    /** @return string */
    public function getSqlUpdate()
    {
        return 'UPDATE pessoas SET nome = :nome, email = :email, telefone = :telefone, cidade_id = :cidade_id WHERE id = :id';
    }

    /**
     * @param Model $Model
     * @return array
     */
    public function getParams(Model $Model)
    {
        return [
            'nome' => $Model->nome,
            'email' => $Model->email,
            'telefone' => $Model->telefone,
            'cidade_id' => $Model->cidade_id ?: null,
        ];
    }

    /**
     * @param string $email
     * @return Pessoa|null
     */
    public function getByEmail($email)
    {
        return $this->getFirstModel(['lower(t.email) = :email'], ['email' => StringUtils::normalizeEmail($email)]);
    }

    /**
     * @param string $name
     * @param string $email
     * @param string|null $phone
     * @param Cidade $Cidade
     * @return Pessoa
     */
    public function register($name, $email, $phone = null, Cidade $Cidade = null)
    {
        $Pessoa = $this->getByEmail($email) ?: new Pessoa(['email' => StringUtils::normalizeEmail($email)]);
        $Pessoa->nome = $name;
        if ($phone !== null) {
            $Pessoa->telefone = $phone;
        }
        if ($Cidade !== null) {
            $Pessoa->cidade_id = $Cidade->getId();
        }
        return $this->save($Pessoa);
    }

    /**
     * @param Pessoa $Pessoa
     * @return void
     */
    public function deletePersonalData(Pessoa $Pessoa)
    {
        Database::transaction(function () use ($Pessoa) {
            Database::execute('DELETE FROM emails_enviados WHERE lower(destinatario) = lower(:email)', ['email' => $Pessoa->email]);
            $this->delete($Pessoa);
        });
    }
}
