<?php

namespace App\Utils;

class StringUtils
{
    /**
     * @param mixed $value
     * @return string
     */
    public static function clean($value)
    {
        return is_string($value) ? trim($value) : '';
    }

    /**
     * @param string $value
     * @return string
     */
    public static function lower($value)
    {
        return strtolower($value);
    }

    /**
     * @param string $value
     * @return int
     */
    public static function length($value)
    {
        return strlen($value);
    }

    /**
     * @param string $value
     * @return string
     */
    public static function digits($value)
    {
        return preg_replace('/\D/', '', $value);
    }

    /**
     * @param string $value
     * @return string|null
     */
    public static function emptyToNull($value)
    {
        $value = self::clean($value);
        return $value === '' ? null : $value;
    }

    /**
     * @param string $email
     * @return string
     */
    public static function normalizeEmail($email)
    {
        return self::lower(self::clean($email));
    }

    /**
     * @param string $value
     * @param int $max
     * @return string
     */
    public static function truncate($value, $max)
    {
        return strlen($value) > $max ? substr($value, 0, $max - 1) . '…' : $value;
    }
}
