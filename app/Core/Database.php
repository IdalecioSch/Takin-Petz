<?php

namespace App\Core;

use App\Utils\PersistenciaUtils;

class Database
{
    private static $Pdo;

    /**
     * @return \PDO
     */
    public static function getPdo()
    {
        if (self::$Pdo === null) {
            $c = Config::get('db');
            $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $c['host'], $c['porta'], $c['nome']);
            self::$Pdo = new \PDO($dsn, $c['usuario'], $c['senha'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }
        return self::$Pdo;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return \PDOStatement
     */
    public static function execute($sql, $params = [])
    {
        $Statement = self::getPdo()->prepare($sql);
        PersistenciaUtils::bind($Statement, PersistenciaUtils::filterParams($sql, $params));
        $Statement->execute();
        return $Statement;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array
     */
    public static function fetchAll($sql, $params = [])
    {
        return self::execute($sql, $params)->fetchAll();
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array|null
     */
    public static function fetchOne($sql, $params = [])
    {
        $row = self::execute($sql, $params)->fetch();
        return $row ?: null;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return mixed
     */
    public static function fetchValue($sql, $params = [])
    {
        return self::execute($sql, $params)->fetchColumn();
    }

    /**
     * @param callable $callback
     * @return mixed
     * @throws \Exception
     */
    public static function transaction($callback)
    {
        $Pdo = self::getPdo();
        if ($Pdo->inTransaction()) {
            return $callback();
        }
        $Pdo->beginTransaction();
        try {
            $result = $callback();
            $Pdo->commit();
            return $result;
        } catch (\Throwable $Exception) {
            if ($Pdo->inTransaction()) {
                $Pdo->rollBack();
            }
            throw $Exception;
        }
    }
}
