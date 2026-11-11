<?php

namespace App\Persistence;

use App\Core\{Model, Persistence};
use App\Models\EmailLog;

class EmailLogPersistence extends Persistence
{
    /** @return string */
    public function getTable() { return 'emails_enviados'; }

    /** @return string */
    public function getModelClass() { return EmailLog::class; }

    /** @return string */
    public function getDefaultOrderBy() { return 't.id DESC'; }

    /** @return string */
    public function getSqlInsert()
    {
        return 'INSERT INTO emails_enviados (destinatario, assunto, sucesso, erro) VALUES (:destinatario, :assunto, :sucesso, :erro)';
    }

    /** @return string */
    public function getSqlUpdate() { return 'UPDATE emails_enviados SET erro = :erro WHERE id = :id'; }

    /**
     * @param Model $Model
     * @return array
     */
    public function getParams(Model $Model)
    {
        return [
            'destinatario' => $Model->destinatario,
            'assunto' => $Model->assunto,
            'sucesso' => (bool)$Model->sucesso,
            'erro' => $Model->erro,
        ];
    }
}
