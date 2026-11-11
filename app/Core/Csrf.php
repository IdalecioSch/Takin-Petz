<?php

namespace App\Core;

class Csrf
{
    /**
     * @return string
     */
    public static function token()
    {
        if (!Session::get('csrf')) {
            Session::set('csrf', bin2hex(random_bytes(32)));
        }
        return (string)Session::get('csrf');
    }

    /**
     * @return string
     */
    public static function field()
    {
        return '<input type="hidden" name="csrf" value="' . htmlspecialchars(self::token(), ENT_QUOTES) . '">';
    }

    /**
     * @return void
     * @throws HttpException
     */
    public static function verify()
    {
        $sent = isset($_POST['csrf']) ? $_POST['csrf'] : '';
        if (!is_string($sent) || !hash_equals((string)Session::get('csrf', ''), $sent)) {
            throw new HttpException(419, Messages::ERROR_CSRF);
        }
    }
}
