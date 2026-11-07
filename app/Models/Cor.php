<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Cor extends Model
{
    protected static string $tabela = 'cores';
    protected static array $colunas = ['nome'];

    public static function obter(?string $nome): ?static
    {
        $nome = trim((string)$nome);
        return $nome === '' ? null : static::obterOuCriar('lower(nome) = lower(:n)', ['n' => $nome], ['nome' => $nome]);
    }
}
