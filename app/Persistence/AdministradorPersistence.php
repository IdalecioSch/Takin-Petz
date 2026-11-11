<?php

namespace App\Persistence;

use App\Core\{Model, Persistence};
use App\Models\Administrador;
use App\Utils\StringUtils;

class AdministradorPersistence extends Persistence
{
    /** @return string */
    public function getTable() { return 'usuarios_admin'; }

    /** @return string */
    public function getModelClass() { return Administrador::class; }

    /** @return string */
    public function getSqlInsert()
    {
        return 'INSERT INTO usuarios_admin (nome, email, senha_hash) VALUES (:nome, :email, :senha_hash)';
    }

    /** @return string */
    public function getSqlUpdate()
    {
        return 'UPDATE usuarios_admin SET nome = :nome, email = :email, senha_hash = :senha_hash, ativo = :ativo WHERE id = :id';
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
            'senha_hash' => $Model->senha_hash,
            'ativo' => $Model->ativo === null ? true : (bool)$Model->ativo,
        ];
    }

    /**
     * @param string $name
     * @param string $email
     * @param string $password
     * @return Administrador
     */
    public function create($name, $email, $password)
    {
        $Admin = new Administrador([
            'nome' => $name,
            'email' => StringUtils::normalizeEmail($email),
            'senha_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        return $this->save($Admin);
    }

    /**
     * @param string $email
     * @param string $password
     * @return Administrador|null
     */
    public function authenticate($email, $password)
    {
        $Admin = $this->getFirstModel(['lower(t.email) = lower(:email)', 't.ativo = true'], ['email' => $email]);
        // hash falso: mantém o tempo de resposta parecido quando o e-mail não existe
        $hash = $Admin ? $Admin->senha_hash : '$2y$10$usesomesillystringforsalt.abcdefghijklmnopqrstuvwxyz012';
        if (!password_verify($password, $hash) || !$Admin) {
            usleep(400000);
            return null;
        }
        if (password_needs_rehash($Admin->senha_hash, PASSWORD_DEFAULT)) {
            $Admin->senha_hash = password_hash($password, PASSWORD_DEFAULT);
            $this->update($Admin);
        }
        return $Admin;
    }
}
