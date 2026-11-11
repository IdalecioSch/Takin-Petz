<?php

namespace App\Core;

class Config
{
    private static $data = [];

    /**
     * @param string $file
     * @return void
     */
    public static function load($file)
    {
        self::$data = require $file;
    }

    /**
     * @param string $path
     * @param mixed $default
     * @return mixed
     */
    public static function get($path, $default = null)
    {
        $value = self::$data;
        foreach (explode('.', $path) as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return $default;
            }
            $value = $value[$key];
        }
        return $value;
    }
}
