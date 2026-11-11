<?php

namespace App\Persistence;

use App\Core\{Model, Persistence};
use App\Models\{MensagemContato, Pessoa};

class MensagemContatoPersistence extends Persistence
{
    /** @return string */
    public function getTable() { return 'mensagens_contato'; }

    /** @return string */
    public function getModelClass() { return MensagemContato::class; }

    /** @return string */
    public function getSqlGetAll()
    {
        return 'SELECT t.*, p.nome AS pessoa_nome, p.email AS pessoa_email
                  FROM mensagens_contato t JOIN pessoas p ON p.id = t.pessoa_id';
    }

    /** @return string */
    public function getSqlInsert() { return 'INSERT INTO mensagens_contato (pessoa_id, mensagem) VALUES (:pessoa_id, :mensagem)'; }

    /** @return string */
    public function getSqlUpdate() { return 'UPDATE mensagens_contato SET mensagem = :mensagem WHERE id = :id'; }

    /**
     * @param Model $Model
     * @return array
     */
    public function getParams(Model $Model) { return ['pessoa_id' => $Model->pessoa_id, 'mensagem' => $Model->mensagem]; }

    /**
     * @param Pessoa $Pessoa
     * @param string $message
     * @return MensagemContato
     */
    public function register(Pessoa $Pessoa, $message)
    {
        return $this->save(new MensagemContato(['pessoa_id' => $Pessoa->getId(), 'mensagem' => $message]));
    }
}
