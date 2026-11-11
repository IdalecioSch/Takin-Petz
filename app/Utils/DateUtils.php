<?php

namespace App\Utils;

class DateUtils
{
    /**
     * @return string
     */
    public static function now()
    {
        return date('Y-m-d H:i:sP');
    }

    /**
     * @return string
     */
    public static function today()
    {
        return date('Y-m-d');
    }

    /**
     * @param string $date
     * @return bool
     */
    public static function isValid($date)
    {
        $D = \DateTime::createFromFormat('Y-m-d', (string)$date);
        return $D !== false && $D->format('Y-m-d') === $date;
    }

    /**
     * @param string|null $timestamp
     * @param bool $withTime
     * @return string
     */
    public static function formatBr($timestamp, $withTime = true)
    {
        if (!$timestamp) {
            return '—';
        }
        return date($withTime ? 'd/m/Y H:i' : 'd/m/Y', strtotime($timestamp));
    }
}
