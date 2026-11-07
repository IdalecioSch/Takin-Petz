<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

/** Acesso ao PostgreSQL via PDO (conexão única, prepared statements sempre). */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $c = Config::get('db');
            $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $c['host'], $c['porta'], $c['nome']);
            self::$pdo = new PDO($dsn, $c['usuario'], $c['senha'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$pdo;
    }

    /** Executa com parâmetros nomeados (com ou sem ':'). bool vira 'true'/'false' (exigência do pdo_pgsql). */
    public static function executar(string $sql, array $params = []): PDOStatement
    {
        $st = self::pdo()->prepare($sql);
        foreach ($params as $nome => $valor) {
            $nome = ':' . ltrim((string)$nome, ':');
            match (true) {
                $valor === null  => $st->bindValue($nome, null, PDO::PARAM_NULL),
                is_bool($valor)  => $st->bindValue($nome, $valor ? 'true' : 'false'),
                is_int($valor)   => $st->bindValue($nome, $valor, PDO::PARAM_INT),
                default          => $st->bindValue($nome, (string)$valor),
            };
        }
        $st->execute();
        return $st;
    }

    public static function todos(string $sql, array $params = []): array
    {
        return self::executar($sql, $params)->fetchAll();
    }

    public static function um(string $sql, array $params = []): ?array
    {
        return self::executar($sql, $params)->fetch() ?: null;
    }

    public static function valor(string $sql, array $params = []): mixed
    {
        return self::executar($sql, $params)->fetchColumn();
    }

    /** Roda $fn dentro de uma transação (reaproveita a transação aberta, se houver). */
    public static function transacao(callable $fn): mixed
    {
        $pdo = self::pdo();
        if ($pdo->inTransaction()) {
            return $fn();
        }
        $pdo->beginTransaction();
        try {
            $resultado = $fn();
            $pdo->commit();
            return $resultado;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
