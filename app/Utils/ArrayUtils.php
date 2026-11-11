<?php

namespace App\Utils;

class ArrayUtils
{
    /**
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * @param array $array
     * @param array $keys
     * @return array
     */
    public static function only($array, $keys)
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * @param array $array
     * @param array $keys
     * @return array
     */
    public static function except($array, $keys)
    {
        return array_diff_key($array, array_flip($keys));
    }

    /**
     * @param array $rows
     * @param string $column
     * @return array
     */
    public static function column($rows, $column)
    {
        return array_column($rows, $column);
    }

    /**
     * @param array $rows
     * @param string $key
     * @return array
     */
    public static function indexBy($rows, $key)
    {
        return array_column($rows, null, $key);
    }

    /**
     * @param array $rows
     * @param string $key
     * @return array
     */
    public static function groupBy($rows, $key)
    {
        $groups = [];
        foreach ($rows as $row) {
            $groups[$row[$key]][] = $row;
        }
        return $groups;
    }

    /**
     * @param mixed $values
     * @return array
     */
    public static function toInts($values)
    {
        return array_values(array_map('intval', array_filter((array)$values, 'is_scalar')));
    }

    /**
     * @param array $array
     * @param mixed $default
     * @return mixed
     */
    public static function first($array, $default = null)
    {
        return $array ? reset($array) : $default;
    }
}
