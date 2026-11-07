<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Cidade extends Model
{
    protected static string $tabela = 'cidades';
    protected static array $colunas = ['nome', 'uf'];

    public static function obter(string $nome, string $uf): static
    {
        return static::obterOuCriar('uf = :uf AND lower(nome) = lower(:n)', ['uf' => $uf, 'n' => $nome], ['nome' => $nome, 'uf' => $uf]);
    }

    public function rotulo(): string
    {
        return $this->nome . '/' . $this->uf;
    }
}
