<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Administrador extends Model
{
    protected static string $tabela = 'usuarios_admin';
    protected static array $colunas = ['nome', 'email', 'senha_hash', 'ativo'];

    public static function criar(string $nome, string $email, string $senha): static
    {
        return (new static([
            'nome' => $nome,
            'email' => mb_strtolower($email),
            'senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
        ]))->salvar();
    }

    public static function autenticar(string $email, string $senha): ?static
    {
        $a = static::primeiro('lower(email) = lower(:e) AND ativo = true', ['e' => $email]);
        // hash falso mantém o tempo de resposta parecido quando o e-mail não existe
        $hash = $a?->senha_hash ?? '$2y$10$usesomesillystringforsalt.abcdefghijklmnopqrstuvwxyz012';
        if (!password_verify($senha, $hash) || $a === null) {
            usleep(400000);
            return null;
        }
        if (password_needs_rehash($a->senha_hash, PASSWORD_DEFAULT)) {
            $a->senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $a->salvar();
        }
        return $a;
    }
}
