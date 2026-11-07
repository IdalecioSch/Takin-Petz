<?php
declare(strict_types=1);

namespace App\Core;

/** Leitura (com cache) das tabelas de domínio: especies, portes, status_animal, etc. */
final class Dominio
{
    /** tabela => coluna da chave */
    private const TABELAS = [
        'especies' => 'codigo', 'sexos' => 'codigo', 'portes' => 'codigo', 'faixas_etarias' => 'codigo',
        'status_animal' => 'codigo', 'status_solicitacao' => 'codigo', 'tipos_moradia' => 'codigo',
        'estados' => 'sigla',
    ];
    private static array $cache = [];

    /** @return array<string,string> codigo => nome */
    public static function opcoes(string $tabela): array
    {
        $chave = self::TABELAS[$tabela] ?? throw new \InvalidArgumentException("Tabela de domínio inválida: $tabela");
        return self::$cache[$tabela] ??= array_column(
            Database::todos("SELECT $chave AS k, nome FROM $tabela ORDER BY ordem, nome"),
            'nome',
            'k'
        );
    }

    public static function nome(string $tabela, ?string $codigo): string
    {
        return self::opcoes($tabela)[$codigo ?? ''] ?? (string)$codigo;
    }

    public static function valido(string $tabela, ?string $codigo): bool
    {
        return $codigo !== null && isset(self::opcoes($tabela)[$codigo]);
    }
}
