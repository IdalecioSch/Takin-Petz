<?php
declare(strict_types=1);

namespace App\Core;

final class Config
{
    private static array $dados = [];

    public static function carregar(string $arquivo): void
    {
        self::$dados = require $arquivo;
    }

    /** Notação de ponto: Config::get('db.host'). */
    public static function get(string $caminho, mixed $padrao = null): mixed
    {
        $v = self::$dados;
        foreach (explode('.', $caminho) as $chave) {
            if (!is_array($v) || !array_key_exists($chave, $v)) {
                return $padrao;
            }
            $v = $v[$chave];
        }
        return $v;
    }
}
