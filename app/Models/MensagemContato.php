<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class MensagemContato extends Model
{
    protected static string $tabela = 'mensagens_contato';
    protected static array $colunas = ['pessoa_id', 'mensagem'];

    public static function registrar(Pessoa $pessoa, string $mensagem): static
    {
        return (new static(['pessoa_id' => $pessoa->id(), 'mensagem' => $mensagem]))->salvar();
    }

    public function pessoa(): ?Pessoa
    {
        return $this->pertenceA(Pessoa::class, 'pessoa_id');
    }
}
