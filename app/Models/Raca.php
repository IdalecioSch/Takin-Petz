<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Raca extends Model
{
    protected static string $tabela = 'racas';
    protected static array $colunas = ['especie', 'nome'];

    public static function obter(string $especie, ?string $nome): ?static
    {
        $nome = trim((string)$nome);
        return $nome === ''
            ? null
            : static::obterOuCriar('especie = :e AND lower(nome) = lower(:n)', ['e' => $especie, 'n' => $nome], ['especie' => $especie, 'nome' => $nome]);
    }
}
