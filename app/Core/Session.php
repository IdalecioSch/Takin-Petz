<?php

namespace App\Core;

class Session
{
    /**
     * @return void
     */
    public static function start()
    {
        ini_set('session.use_strict_mode', '1');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']),
        ]);
        session_start();
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @param string $type
     * @param string $message
     * @return void
     */
    public static function flash($type, $message)
    {
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }

    /**
     * @return array
     */
    public static function pullFlashes()
    {
        $flashes = self::get('_flash', []);
        unset($_SESSION['_flash']);
        return $flashes;
    }

    /**
     * @return void
     */
    public static function regenerate()
    {
        session_regenerate_id(true);
    }

    /**
     * @return void
     */
    public static function destroy()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], isset($p['domain']) ? $p['domain'] : '', $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}
