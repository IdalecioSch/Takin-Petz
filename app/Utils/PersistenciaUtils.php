<?php

namespace App\Utils;

class PersistenciaUtils
{
    /**
     * @param string $sql
     * @param array $params
     * @return array
     */
    public static function filterParams($sql, $params)
    {
        preg_match_all('/(?<!:):([a-zA-Z_]\w*)/', $sql, $found);
        return ArrayUtils::only($params, array_unique($found[1]));
    }

    /**
     * @param \PDOStatement $Statement
     * @param array $params
     * @return void
     */
    public static function bind(\PDOStatement $Statement, $params)
    {
        foreach ($params as $name => $value) {
            $name = ':' . ltrim((string)$name, ':');
            if ($value === null) {
                $Statement->bindValue($name, null, \PDO::PARAM_NULL);
            } elseif (is_bool($value)) {
                $Statement->bindValue($name, $value ? 'true' : 'false');
            } elseif (is_int($value)) {
                $Statement->bindValue($name, $value, \PDO::PARAM_INT);
            } else {
                $Statement->bindValue($name, (string)$value);
            }
        }
    }

    /**
     * @param string|array $conditions
     * @return string
     */
    public static function buildWhere($conditions)
    {
        $conditions = array_filter((array)$conditions);
        return $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
    }

    /**
     * @param string $text
     * @return string
     */
    public static function like($text)
    {
        return '%' . addcslashes($text, '%_\\') . '%';
    }

    /**
     * @param array $values
     * @param string $prefix
     * @return array
     */
    public static function inList($values, $prefix = 'p')
    {
        $names = [];
        $params = [];
        foreach (array_values($values) as $i => $value) {
            $names[] = ':' . $prefix . $i;
            $params[$prefix . $i] = $value;
        }
        return [implode(', ', $names), $params];
    }

    /**
     * @param \Exception $Exception
     * @return bool
     */
    public static function isUniqueViolation($Exception)
    {
        return $Exception instanceof \PDOException && $Exception->getCode() === '23505';
    }

    /**
     * @param \Exception $Exception
     * @return bool
     */
    public static function isForeignKeyViolation($Exception)
    {
        return $Exception instanceof \PDOException && $Exception->getCode() === '23503';
    }
}
