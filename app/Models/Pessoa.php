<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\{Database, Model};

/** Quem pede adoção ou envia mensagem. Identificada pelo e-mail. */
class Pessoa extends Model
{
    protected static string $tabela = 'pessoas';
    protected static array $colunas = ['nome', 'email', 'telefone', 'cidade_id'];

    /** Localiza pela e-mail (sem diferenciar maiúsculas) ou cria; atualiza os dados informados. */
    public static function registrar(string $nome, string $email, ?string $telefone = null, ?Cidade $cidade = null): static
    {
        $email = mb_strtolower($email);
        $p = static::primeiro('lower(email) = :e', ['e' => $email]) ?? new static(['email' => $email]);
        $p->nome = $nome;
        if ($telefone !== null) {
            $p->telefone = $telefone;
        }
        if ($cidade !== null) {
            $p->cidade_id = $cidade->id();
        }
        return $p->salvar();
    }

    public function cidade(): ?Cidade
    {
        return $this->pertenceA(Cidade::class, 'cidade_id');
    }

    /** @return Solicitacao[] */
    public function solicitacoes(): array
    {
        return $this->temMuitos(Solicitacao::class, 'pessoa_id', 'criado_em DESC');
    }

    /** LGPD (RN22): apaga a pessoa, seus pedidos e mensagens (cascade) e o log de e-mails dela. */
    public function excluirDados(): void
    {
        Database::transacao(function (): void {
            Database::executar('DELETE FROM emails_enviados WHERE lower(destinatario) = lower(:e)', ['e' => $this->email]);
            $this->excluir();
        });
    }
}
