<?php

namespace App\Core;

/** Tabelas de domínio (valores fixos): leitura com cache. */
class Domain
{
    private static $tables = [
        'especies' => 'codigo', 'sexos' => 'codigo', 'portes' => 'codigo', 'faixas_etarias' => 'codigo',
        'status_animal' => 'codigo', 'status_solicitacao' => 'codigo', 'tipos_moradia' => 'codigo',
        'tipos_alimentacao' => 'codigo', 'estados' => 'sigla',
    ];
    private static $cache = [];

    /**
     * @param string $table
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function getOptions($table)
    {
        if (!isset(self::$tables[$table])) {
            throw new \InvalidArgumentException('Tabela de domínio inválida: ' . $table);
        }
        if (!isset(self::$cache[$table])) {
            $rows = Database::fetchAll('SELECT ' . self::$tables[$table] . ' AS code, nome FROM ' . $table . ' ORDER BY ordem, nome');
            self::$cache[$table] = array_column($rows, 'nome', 'code');
        }
        return self::$cache[$table];
    }

    /**
     * @param string $table
     * @param string|null $code
     * @return string
     */
    public static function getLabel($table, $code)
    {
        $options = self::getOptions($table);
        return isset($options[$code]) ? $options[$code] : (string)$code;
    }

    /**
     * @param string $table
     * @param string|null $code
     * @return bool
     */
    public static function isValid($table, $code)
    {
        return $code !== null && isset(self::getOptions($table)[$code]);
    }
}
